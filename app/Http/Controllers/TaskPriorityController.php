<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskPriorityGroupUpdateRequest;
use App\Http\Requests\TaskPriorityStoreRequest;
use App\Models\Instruction;
use App\Models\TaskPriority;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TaskPriorityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        // Only show task priorities for instructions where the current user is a recipient
        $baseQuery = TaskPriority::query()
            ->whereHas('instruction.recipients', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });

        // Auto-limit to the most recent sender who sent an instruction to the current user
        $recentSenderId = TaskPriority::query()
            ->whereHas('instruction.recipients', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orderByDesc('created_at')
            ->value('instruction_sender_id');

        if ($recentSenderId) {
            $baseQuery->where('instruction_sender_id', $recentSenderId);
        }

        // Apply filters
        if ($request->filled('instruction_title')) {
            $baseQuery->whereHas('instruction', function ($q) use ($request) {
                $q->where('title', 'like', '%'.$request->instruction_title.'%');
            });
        }

        // Sender filter removed to keep UI as a single search bar and always scoped to recent sender

        if ($request->filled('priority_level')) {
            $baseQuery->where('priority_level', $request->priority_level);
        }

        if ($request->filled('status')) {
            $baseQuery->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $baseQuery->where('target_deadline', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $baseQuery->where('target_deadline', '<=', $request->date_to);
        }

        // Paginate by distinct group_key (one row per group)
        $perPage = 15;
        $page = (int) ($request->input('page', 1));

        $groupKeysQuery = (clone $baseQuery)->select('group_key')->distinct();
        $totalGroups = (clone $groupKeysQuery)->count('group_key');
        $groupKeys = (clone $groupKeysQuery)
            ->orderBy('group_key')
            ->forPage($page, $perPage)
            ->pluck('group_key');

        $representatives = TaskPriority::with(['instruction', 'sender', 'createdBy'])
            ->whereIn('group_key', $groupKeys)
            ->get()
            ->groupBy('group_key')
            ->map->first()
            ->values();

        // Determine which groups are fully accomplished (deletable)
        $deletableGroupKeys = TaskPriority::select('group_key')
            ->whereIn('group_key', $groupKeys)
            ->groupBy('group_key')
            ->havingRaw("SUM(CASE WHEN status <> 'Accomplished' THEN 1 ELSE 0 END) = 0")
            ->pluck('group_key')
            ->toArray();

        $taskPriorities = new \Illuminate\Pagination\LengthAwarePaginator(
            $representatives,
            $totalGroups,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('task-priorities.index', compact('taskPriorities', 'deletableGroupKeys'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $user = auth()->user();

        // Only get instructions where the current user is a recipient
        $instructions = Instruction::with('sender')
            ->whereHas('recipients', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get()
            ->map(function ($instruction) {
                return [
                    'id' => $instruction->id,
                    'title' => $instruction->title,
                    'sender_id' => $instruction->sender_id,
                    'sender_name' => $instruction->sender->full_name,
                    'sender_avatar' => $instruction->sender->avatar_url,
                ];
            });

        return view('task-priorities.create', compact('instructions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TaskPriorityStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = auth()->user();

        // Get the instruction and verify the user is a recipient
        $instruction = Instruction::with('sender')
            ->whereHas('recipients', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->findOrFail($data['instruction_id']);

        $created = [];

        DB::transaction(function () use ($data, $instruction, &$created, $user) {
            $groupKey = (string) \Illuminate\Support\Str::uuid();
            foreach ($data['items'] as $item) {
                $weekRange = TaskPriority::calculateWeekRange(
                    $item['start_date'],
                    $item['target_deadline']
                );

                $created[] = TaskPriority::create([
                    'group_key' => $groupKey,
                    'instruction_id' => $instruction->id,
                    'instruction_sender_id' => $instruction->sender_id, // Use instruction's sender
                    'created_by_user_id' => $user->id,
                    'priority_title' => $item['priority_title'],
                    'priority_level' => $item['priority_level'],
                    'start_date' => $item['start_date'],
                    'target_deadline' => $item['target_deadline'],
                    'week_range' => $weekRange,
                    'status' => $item['status'] ?? 'Not Started',
                    'notes' => $item['notes'] ?? null,
                ]);
            }
        });

        // Notify the instruction sender in real-time across channels (no queue delay)
        try {
            $sender = $instruction->sender; // User model
            if ($sender) {
                $sender->notifyNow(new \App\Notifications\TaskPriorityCreated($instruction, collect($created), $user));
            }
        } catch (\Throwable $e) {
            \Log::warning('Failed to notify sender about task priority creation', [
                'instruction_id' => $instruction->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('task-priorities.index')
            ->with('success', 'Task priorities created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TaskPriority $taskPriority): View
    {
        $user = auth()->user();

        $isRecipient = $taskPriority->instruction->recipients()->where('user_id', $user->id)->exists();
        $isSender = (int) ($taskPriority->instruction->sender_id ?? 0) === (int) $user->id;
        if (! $isRecipient && ! $isSender) {
            abort(403, 'You can only view task priorities for instructions assigned to you or those you sent.');
        }

        $taskPriority->load(['instruction', 'sender']);
        $groupItems = TaskPriority::with(['instruction', 'sender'])
            ->where('group_key', $taskPriority->group_key)
            ->orderBy('id')
            ->get();

        return view('task-priorities.show', [
            'taskPriority' => $taskPriority,
            'groupItems' => $groupItems,
            'canModify' => $isRecipient, // senders have read-only access
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TaskPriority $taskPriority): View
    {
        $user = auth()->user();

        // Verify the user is a recipient of the instruction
        if (! $taskPriority->instruction->recipients()->where('user_id', $user->id)->exists()) {
            abort(403, 'You can only edit task priorities for instructions assigned to you.');
        }

        $taskPriority->load(['instruction', 'sender']);
        $groupItems = TaskPriority::where('group_key', $taskPriority->group_key)
            ->orderBy('id')
            ->get();

        return view('task-priorities.edit', [
            'taskPriority' => $taskPriority,
            'groupItems' => $groupItems,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TaskPriorityGroupUpdateRequest $request, TaskPriority $taskPriority): RedirectResponse
    {
        $user = auth()->user();

        // Verify the user is a recipient of the instruction
        if (! $taskPriority->instruction->recipients()->where('user_id', $user->id)->exists()) {
            abort(403, 'You can only update task priorities for instructions assigned to you.');
        }

        $data = $request->validated();

        DB::transaction(function () use ($data, $taskPriority) {
            // Remove existing items in the group
            TaskPriority::where('group_key', $taskPriority->group_key)->delete();

            // Recreate the items for the group
            foreach ($data['items'] as $item) {
                $weekRange = TaskPriority::calculateWeekRange(
                    $item['start_date'],
                    $item['target_deadline']
                );

                TaskPriority::create([
                    'group_key' => $taskPriority->group_key,
                    'instruction_id' => $taskPriority->instruction_id,
                    'instruction_sender_id' => $taskPriority->instruction_sender_id,
                    'created_by_user_id' => $taskPriority->created_by_user_id,
                    'priority_title' => $item['priority_title'],
                    'priority_level' => $item['priority_level'],
                    'start_date' => $item['start_date'],
                    'target_deadline' => $item['target_deadline'],
                    'week_range' => $weekRange,
                    'status' => $item['status'] ?? 'Not Started',
                    'notes' => $item['notes'] ?? null,
                ]);
            }
        });

        // Gather updated items and notify instruction sender instantly (email, in-app, broadcast, Telegram)
        try {
            $instruction = $taskPriority->instruction()->with('sender')->first();
            $updatedItems = TaskPriority::where('group_key', $taskPriority->group_key)
                ->orderBy('id')
                ->get();
            if ($instruction && $instruction->sender) {
                $instruction->sender->notifyNow(new \App\Notifications\TaskPriorityUpdated($instruction, $updatedItems, $user));
            }
        } catch (\Throwable $e) {
            \Log::warning('Failed to notify sender about task priority update', [
                'group_key' => $taskPriority->group_key,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('task-priorities.index')
            ->with('success', 'Task priority updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TaskPriority $taskPriority): RedirectResponse
    {
        $user = auth()->user();

        // Verify the user is a recipient of the instruction
        if (! $taskPriority->instruction->recipients()->where('user_id', $user->id)->exists()) {
            abort(403, 'You can only delete task priorities for instructions assigned to you.');
        }

        TaskPriority::where('group_key', $taskPriority->group_key)->delete();

        return redirect()
            ->route('task-priorities.index')
            ->with('success', 'Task priority deleted successfully.');
    }

    /**
     * Bulk delete task priorities.
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $request->validate([
            'selected_items' => 'required|array|min:1',
            'selected_items.*' => 'exists:task_priorities,id',
        ]);

        // Resolve selected group keys
        $selectedGroupKeys = TaskPriority::whereIn('id', $request->selected_items)
            ->pluck('group_key')
            ->unique()
            ->values();

        // Filter to groups belonging to instructions where user is a recipient and all items are Accomplished
        $deletableGroupKeys = TaskPriority::select('group_key')
            ->whereIn('group_key', $selectedGroupKeys)
            ->whereHas('instruction.recipients', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->groupBy('group_key')
            ->havingRaw("SUM(CASE WHEN status <> 'Accomplished' THEN 1 ELSE 0 END) = 0")
            ->pluck('group_key');

        if ($deletableGroupKeys->isEmpty()) {
            return redirect()
                ->route('task-priorities.index')
                ->with('error', 'No selected groups are eligible for deletion. Only groups where all items are Accomplished can be deleted.');
        }

        $deletedCount = TaskPriority::whereIn('group_key', $deletableGroupKeys)->delete();

        return redirect()
            ->route('task-priorities.index')
            ->with('success', "{$deletedCount} task priorities deleted successfully.");
    }

    /**
     * Read-only listing for instruction senders: shows groups created by recipients for their instructions.
     */
    public function sent(Request $request): View
    {
        $user = auth()->user();

        // Base query: groups for instructions where current user is the sender
        $baseQuery = TaskPriority::query()
            ->where('instruction_sender_id', $user->id);

        // Simple search across instruction title and receiver name
        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $baseQuery->where(function ($query) use ($search) {
                $query->whereHas('instruction', function ($q) use ($search) {
                    $q->where('title', 'like', '%'.$search.'%');
                })
                    ->orWhereHas('createdBy', function ($q) use ($search) {
                        $q->where('first_name', 'like', '%'.$search.'%')
                            ->orWhere('last_name', 'like', '%'.$search.'%')
                            ->orWhereRaw("TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) LIKE ?", ['%'.$search.'%']);
                    });
            });
        }

        // Per-page with clamping: min 5, max 10
        $perPageInput = (int) $request->input('per_page', 10);
        if ($perPageInput < 5) {
            $perPageInput = 5;
        } elseif ($perPageInput > 10) {
            $perPageInput = 10;
        }
        $perPage = $perPageInput;
        $page = (int) ($request->input('page', 1));

        $groupKeysQuery = (clone $baseQuery)->select('group_key')->distinct();
        $totalGroups = (clone $groupKeysQuery)->count('group_key');
        $groupKeys = (clone $groupKeysQuery)
            ->orderBy('group_key')
            ->forPage($page, $perPage)
            ->pluck('group_key');

        $representatives = TaskPriority::with(['instruction', 'sender', 'createdBy'])
            ->whereIn('group_key', $groupKeys)
            ->get()
            ->groupBy('group_key')
            ->map->first()
            ->values();

        $taskPriorities = new \Illuminate\Pagination\LengthAwarePaginator(
            $representatives,
            $totalGroups,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $readOnly = true;

        // Return partial table for AJAX/live updates
        if ($request->boolean('partial')) {
            return view('task-priorities.partials._sent_table', compact('taskPriorities'));
        }

        return view('task-priorities.sent', compact('taskPriorities', 'readOnly'));
    }

    /**
     * Recycle Bin: list soft-deleted task priority groups for current user.
     */
    public function recycleBin(Request $request): View
    {
        $user = auth()->user();

        $baseQuery = TaskPriority::onlyTrashed()
            ->whereHas('instruction.recipients', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });

        // Group by group_key and show representative row
        $perPage = 15;
        $page = (int) ($request->input('page', 1));

        $groupKeysQuery = (clone $baseQuery)->select('group_key')->distinct();
        $totalGroups = (clone $groupKeysQuery)->count('group_key');
        $groupKeys = (clone $groupKeysQuery)
            ->orderBy('group_key')
            ->forPage($page, $perPage)
            ->pluck('group_key');

        $representatives = TaskPriority::withTrashed()->with(['instruction', 'sender'])
            ->whereIn('group_key', $groupKeys)
            ->get()
            ->groupBy('group_key')
            ->map->first()
            ->values();

        $taskPriorities = new \Illuminate\Pagination\LengthAwarePaginator(
            $representatives,
            $totalGroups,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('task-priorities.recycle-bin', compact('taskPriorities'));
    }

    /**
     * Restore a soft-deleted group by group_key.
     */
    public function restoreGroup(Request $request, string $groupKey): RedirectResponse
    {
        $user = auth()->user();

        // Ensure the group belongs to user's received instructions
        $exists = TaskPriority::onlyTrashed()
            ->where('group_key', $groupKey)
            ->whereHas('instruction.recipients', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->exists();

        if (! $exists) {
            return redirect()->route('task-priorities.recycle-bin')
                ->with('error', 'Group not found or not authorized.');
        }

        TaskPriority::onlyTrashed()->where('group_key', $groupKey)->restore();

        return redirect()->route('task-priorities.recycle-bin')
            ->with('success', 'Task priority group restored successfully.');
    }

    /**
     * Permanently delete a soft-deleted group by group_key.
     */
    public function forceDeleteGroup(Request $request, string $groupKey): RedirectResponse
    {
        $user = auth()->user();

        $query = TaskPriority::onlyTrashed()
            ->where('group_key', $groupKey)
            ->whereHas('instruction.recipients', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });

        if (! $query->exists()) {
            return redirect()->route('task-priorities.recycle-bin')
                ->with('error', 'Group not found or not authorized.');
        }

        $deleted = 0;
        DB::transaction(function () use (&$deleted, $query) {
            $deleted = (clone $query)->forceDelete();
        });

        return redirect()->route('task-priorities.recycle-bin')
            ->with('success', $deleted.' task priorities permanently deleted.');
    }

    /**
     * Export the specified group's items as a styled Excel worksheet (XLSX format).
     * Columns: Priority Title, Status, Target Deadline, Notes
     */
    public function exportGroup(TaskPriority $taskPriority)
    {
        $user = auth()->user();

        // Access control: allow both recipients and the instruction sender
        $isRecipient = $taskPriority->instruction->recipients()->where('user_id', $user->id)->exists();
        $isSender = (int) ($taskPriority->instruction->sender_id ?? 0) === (int) $user->id;
        if (! $isRecipient && ! $isSender) {
            abort(403, 'You can only export task priorities for instructions you sent or were assigned to.');
        }

        $groupKey = $taskPriority->group_key;
        $items = TaskPriority::where('group_key', $groupKey)
            ->orderBy('id')
            ->get();

        $fileName = 'task-priority-group-'.$groupKey.'-'.now()->format('Ymd_His').'.xlsx';

        // Prepared By: prefer the group's creator (created_by_user_id), otherwise fallback to current user
        $creatorName = null;
        $creatorId = (int) ($items->first()->created_by_user_id ?? 0);
        if ($creatorId > 0) {
            $creator = User::find($creatorId);
            if ($creator) {
                $creatorName = trim(($creator->full_name ?? ($creator->name ?? '')));
            }
        }
        $preparedBy = $creatorName ?: trim(($user->full_name ?? ($user->name ?? 'Unknown User')));
        $generatedAt = now()->format('Y-m-d H:i');

        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Task Priorities');

        // Set header information
        $sheet->setCellValue('A1', 'This file was generated by RCS App');
        $sheet->setCellValue('A2', 'Prepared By: '.$preparedBy);
        $sheet->setCellValue('A3', 'Generated At: '.$generatedAt);

        // Style the header information
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F3F4F6');
        $sheet->getStyle('A1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $sheet->getStyle('A2:A3')->getFont()->setSize(10);
        $sheet->getStyle('A2:A3')->getFont()->setBold(true);

        // Set column headers
        $headers = ['Priority Title', 'Status', 'Target Deadline', 'Notes'];
        $headerRow = 5;
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col.$headerRow, $header);
            $col++;
        }

        // Style the column headers
        $headerRange = 'A'.$headerRow.':D'.$headerRow;
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFont()->setSize(12);
        $sheet->getStyle($headerRange)->getFont()->setColor(new Color('FFFFFF'));
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('0EA5E9');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Add data rows
        $dataRow = $headerRow + 1;
        foreach ($items as $item) {
            $sheet->setCellValue('A'.$dataRow, $item->priority_title);
            $sheet->setCellValue('B'.$dataRow, $item->status);
            $sheet->setCellValue('C'.$dataRow, $item->target_deadline ? $item->target_deadline->format('Y-m-d') : '');
            $sheet->setCellValue('D'.$dataRow, $item->notes ?? '');

            // Style the status cell based on status
            $statusCell = 'B'.$dataRow;
            $status = strtolower($item->status);
            if ($status === 'accomplished') {
                $sheet->getStyle($statusCell)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('DCFCE7');
                $sheet->getStyle($statusCell)->getFont()->setColor(new Color('166534'));
            } elseif ($status === 'processing' || $status === 'in progress') {
                $sheet->getStyle($statusCell)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('DBEAFE');
                $sheet->getStyle($statusCell)->getFont()->setColor(new Color('1E40AF'));
            } else {
                $sheet->getStyle($statusCell)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('E5E7EB');
                $sheet->getStyle($statusCell)->getFont()->setColor(new Color('374151'));
            }

            $sheet->getStyle($statusCell)->getFont()->setBold(true);

            // Style the title cell
            $sheet->getStyle('A'.$dataRow)->getFont()->setBold(true);

            // Add borders to all cells
            $rowRange = 'A'.$dataRow.':D'.$dataRow;
            $sheet->getStyle($rowRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            // Alternate row background
            if ($dataRow % 2 == 0) {
                $sheet->getStyle($rowRange)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F9FAFB');
            }

            $dataRow++;
        }

        // Auto-size columns
        foreach (range('A', 'D') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Set minimum column widths
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(30);

        // Create writer and save to temporary file
        $writer = new Xlsx($spreadsheet);

        // Create temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'task_priority_export_');
        $writer->save($tempFile);

        // Return file download response
        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ])->deleteFileAfterSend(true);
    }
}
