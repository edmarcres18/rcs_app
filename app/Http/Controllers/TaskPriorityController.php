<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskPriorityGroupUpdateRequest;
use App\Http\Requests\TaskPriorityStoreRequest;
use App\Models\Instruction;
use App\Models\TaskPriority;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TaskPriorityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * PRIVACY GUARANTEE: Only shows task priorities created by the authenticated user.
     * Even if multiple users receive the same instruction and create task priorities,
     * each user will ONLY see their own task priorities.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        // CRITICAL PRIVACY FILTER: Only show task priorities created by the authenticated user
        // This ensures complete privacy - users only see their own task priorities,
        // even if other recipients of the same instruction also created task priorities
        $baseQuery = TaskPriority::query()
            ->where('created_by_user_id', $user->id)
            ->whereHas('instruction.recipients', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });

        // Auto-limit to the most recent sender who sent an instruction to the current user
        // and for which the current user has created task priorities
        $recentSenderId = TaskPriority::query()
            ->where('created_by_user_id', $user->id)
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

        // Fetch representatives with privacy filter
        $representatives = TaskPriority::with(['instruction', 'sender:id,first_name,middle_name,last_name,avatar', 'createdBy'])
            ->where('created_by_user_id', $user->id)
            ->whereIn('group_key', $groupKeys)
            ->get()
            ->groupBy('group_key')
            ->map->first()
            ->values();

        // Determine which groups are fully accomplished (deletable)
        // Apply privacy filter here as well
        $deletableGroupKeys = TaskPriority::select('group_key')
            ->where('created_by_user_id', $user->id)
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
        $instructions = Instruction::with('sender:id,first_name,middle_name,last_name,avatar')
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
                    'sender_avatar' => $instruction->sender->avatar,
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
        $instruction = Instruction::with('sender:id,first_name,middle_name,last_name,avatar')
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
     *
     * PRIVACY GUARANTEE: Only the creator of the task priority can view it,
     * OR the instruction sender can view it (read-only).
     */
    public function show(TaskPriority $taskPriority): View
    {
        $user = auth()->user();

        // Check if user is the creator of this task priority
        $isCreator = (int) ($taskPriority->created_by_user_id ?? 0) === (int) $user->id;

        // Check if user is the instruction sender (read-only access)
        $isSender = (int) ($taskPriority->instruction->sender_id ?? 0) === (int) $user->id;

        // CRITICAL PRIVACY CHECK: Only creator or sender can view
        if (! $isCreator && ! $isSender) {
            abort(403, 'You can only view task priorities you created or those created for instructions you sent.');
        }

        $taskPriority->load(['instruction', 'sender:id,first_name,middle_name,last_name,avatar', 'createdBy']);

        // Fetch all items in the group (no need for additional privacy filter since access already checked above)
        $groupItems = TaskPriority::with(['instruction', 'sender:id,first_name,middle_name,last_name,avatar', 'createdBy'])
            ->where('group_key', $taskPriority->group_key)
            ->orderBy('id')
            ->get();

        return view('task-priorities.show', [
            'taskPriority' => $taskPriority,
            'groupItems' => $groupItems,
            'canModify' => $isCreator, // only creators can modify, senders have read-only access
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * PRIVACY GUARANTEE: Only the creator can edit their own task priorities.
     */
    public function edit(TaskPriority $taskPriority): View
    {
        $user = auth()->user();

        // CRITICAL PRIVACY CHECK: Only the creator can edit
        if ((int) ($taskPriority->created_by_user_id ?? 0) !== (int) $user->id) {
            abort(403, 'You can only edit task priorities you created.');
        }

        $taskPriority->load(['instruction', 'sender', 'createdBy']);
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
     *
     * PRIVACY GUARANTEE: Only the creator can update their own task priorities.
     */
    public function update(TaskPriorityGroupUpdateRequest $request, TaskPriority $taskPriority): RedirectResponse
    {
        $user = auth()->user();

        // CRITICAL PRIVACY CHECK: Only the creator can update
        if ((int) ($taskPriority->created_by_user_id ?? 0) !== (int) $user->id) {
            abort(403, 'You can only update task priorities you created.');
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
            $instruction = $taskPriority->instruction()->with('sender:id,first_name,middle_name,last_name,avatar')->first();
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
     *
     * PRIVACY GUARANTEE: Only the creator can delete their own task priorities.
     */
    public function destroy(TaskPriority $taskPriority): RedirectResponse
    {
        $user = auth()->user();

        // CRITICAL PRIVACY CHECK: Only the creator can delete
        if ((int) ($taskPriority->created_by_user_id ?? 0) !== (int) $user->id) {
            abort(403, 'You can only delete task priorities you created.');
        }

        TaskPriority::where('group_key', $taskPriority->group_key)->delete();

        return redirect()
            ->route('task-priorities.index')
            ->with('success', 'Task priority deleted successfully.');
    }

    /**
     * Bulk delete task priorities.
     *
     * PRIVACY GUARANTEE: Only deletes task priorities created by the authenticated user.
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $request->validate([
            'selected_items' => 'required|array|min:1',
            'selected_items.*' => 'exists:task_priorities,id',
        ]);

        // Resolve selected group keys - CRITICAL: Only for items created by current user
        $selectedGroupKeys = TaskPriority::whereIn('id', $request->selected_items)
            ->where('created_by_user_id', $user->id)
            ->pluck('group_key')
            ->unique()
            ->values();

        // Filter to groups created by user where all items are Accomplished
        // Double-check privacy: only groups created by the current user
        $deletableGroupKeys = TaskPriority::select('group_key')
            ->where('created_by_user_id', $user->id)
            ->whereIn('group_key', $selectedGroupKeys)
            ->groupBy('group_key')
            ->havingRaw("SUM(CASE WHEN status <> 'Accomplished' THEN 1 ELSE 0 END) = 0")
            ->pluck('group_key');

        if ($deletableGroupKeys->isEmpty()) {
            return redirect()
                ->route('task-priorities.index')
                ->with('error', 'No selected groups are eligible for deletion. Only groups where all items are Accomplished can be deleted.');
        }

        // Final privacy check: only delete items created by current user
        $deletedCount = TaskPriority::where('created_by_user_id', $user->id)
            ->whereIn('group_key', $deletableGroupKeys)
            ->delete();

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

        $representatives = TaskPriority::with(['instruction', 'sender:id,first_name,middle_name,last_name,avatar', 'createdBy'])
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
     *
     * PRIVACY GUARANTEE: Only shows soft-deleted task priorities created by the authenticated user.
     */
    public function recycleBin(Request $request): View
    {
        $user = auth()->user();

        // CRITICAL PRIVACY FILTER: Only show soft-deleted items created by current user
        $baseQuery = TaskPriority::onlyTrashed()
            ->where('created_by_user_id', $user->id)
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

        // Fetch representatives with privacy filter
        $representatives = TaskPriority::withTrashed()->with(['instruction', 'sender:id,first_name,middle_name,last_name,avatar', 'createdBy'])
            ->where('created_by_user_id', $user->id)
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
     *
     * PRIVACY GUARANTEE: Only restores task priorities created by the authenticated user.
     */
    public function restoreGroup(Request $request, string $groupKey): RedirectResponse
    {
        $user = auth()->user();

        // CRITICAL PRIVACY CHECK: Ensure the group was created by current user
        $exists = TaskPriority::onlyTrashed()
            ->where('group_key', $groupKey)
            ->where('created_by_user_id', $user->id)
            ->exists();

        if (! $exists) {
            return redirect()->route('task-priorities.recycle-bin')
                ->with('error', 'Group not found or not authorized.');
        }

        // Restore only items created by current user
        TaskPriority::onlyTrashed()
            ->where('group_key', $groupKey)
            ->where('created_by_user_id', $user->id)
            ->restore();

        return redirect()->route('task-priorities.recycle-bin')
            ->with('success', 'Task priority group restored successfully.');
    }

    /**
     * Permanently delete a soft-deleted group by group_key.
     *
     * PRIVACY GUARANTEE: Only permanently deletes task priorities created by the authenticated user.
     */
    public function forceDeleteGroup(Request $request, string $groupKey): RedirectResponse
    {
        $user = auth()->user();

        // CRITICAL PRIVACY CHECK: Only delete items created by current user
        $query = TaskPriority::onlyTrashed()
            ->where('group_key', $groupKey)
            ->where('created_by_user_id', $user->id);

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
     * Matches the exact layout and styling from the reference image.
     *
     * Layout:
     * - Row 1: User name (col B) | Position/Role (cols C-E)
     * - Row 2: Empty
     * - Row 3: Priority level header (cols C-E)
     * - Row 4: Column headers (ITEM NO., TASK, TARGET DATE TO COMPLETED, REMARKS, STATUS)
     * - Row 5+: Data rows
     *
     * PRIVACY GUARANTEE: Only the creator or the instruction sender can export.
     */
    public function exportGroup(TaskPriority $taskPriority)
    {
        try {
            $user = auth()->user();

            // CRITICAL PRIVACY CHECK: Only creator or instruction sender can export
            $isCreator = (int) ($taskPriority->created_by_user_id ?? 0) === (int) $user->id;
            $isSender = (int) ($taskPriority->instruction->sender_id ?? 0) === (int) $user->id;

            if (! $isCreator && ! $isSender) {
                abort(403, 'You can only export task priorities you created or those created for instructions you sent.');
            }

            $groupKey = $taskPriority->group_key;
            $items = TaskPriority::where('group_key', $groupKey)
                ->orderBy('id')
                ->get();

            if ($items->isEmpty()) {
                abort(404, 'No task priorities found in this group.');
            }

            // Check for reasonable export size to prevent memory issues
            if ($items->count() > 1000) {
                throw new \Exception('Export too large. Please contact support for exports with more than 1000 items.');
            }

            // Set memory limit for large exports
            ini_set('memory_limit', '512M');
            ini_set('max_execution_time', 300); // 5 minutes timeout

            // Get creator information with null safety
            $creatorId = (int) ($items->first()->created_by_user_id ?? 0);
            $creator = User::find($creatorId);

            // Prepare user information for header with null safety
            $userName = strtoupper(trim($creator ? $creator->full_name : 'Unknown User'));
            $userPosition = strtoupper(trim($creator ? $creator->position : 'N/A'));

            // Sanitize filename to prevent issues with special characters
            $sanitizedPosition = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $userPosition);
            $fileName = 'WEEKLY LIST OF PRIORITIES - '.$sanitizedPosition.' - '.now()->format('Ymd_His').'.xlsx';

            // Determine priority level category (SHORT TERM, MEDIUM TERM, or LONG TERM)
            $weekRange = $items->first()->week_range ?? 1;
            $priorityCategory = match (true) {
                $weekRange <= 1 => 'SHORT TERM (Weekly)',
                $weekRange <= 3 => 'MEDIUM TERM (Bi-Weekly)',
                default => 'LONG TERM (Monthly)',
            };

            // Create new Spreadsheet object with error handling
            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();

            // Sanitize sheet title to prevent Excel errors
            $sheetTitle = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $userPosition);
            $sheet->setTitle(substr($sheetTitle, 0, 31)); // Excel sheet name limit

            // Set default font to Calibri size 11
            $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri');
            $spreadsheet->getDefaultStyle()->getFont()->setSize(11);

            // ==================== ROW 2: User Name and Position ====================
            // Column B2: User Name
            $sheet->setCellValue('B2', $userName);
            $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('B2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            // Columns C2-E2: Position merged
            $sheet->mergeCells('C2:E2');
            $sheet->setCellValue('C2', $userPosition);
            $sheet->getStyle('C2')->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('C2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            // Apply light blue background to row 2 (user name and position)
            $sheet->getStyle('A2:E2')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('B3D9FF'); // Light blue background

            // Apply borders to row 2
            $sheet->getStyle('A2:E2')->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)
                ->setColor(new Color('000000'));

            // ==================== ROW 3: Priority Category Header ====================
            // Merge cells C3:E3 for priority category
            $sheet->mergeCells('C3:E3');
            $sheet->setCellValue('C3', $priorityCategory);
            $sheet->getStyle('C3')->getFont()->setBold(true)->setSize(11);
            $sheet->getStyle('C3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('C3')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('FFFF00'); // Yellow background

            // Apply borders to row 3
            $sheet->getStyle('A3:E3')->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)
                ->setColor(new Color('000000'));

            // ==================== ROW 4: Column Headers ====================
            $headers = [
                'A4' => 'ITEM NO.',
                'B4' => 'TASK',
                'C4' => 'TARGET DATE TO COMPLETED',
                'D4' => 'REMARKS',
                'E4' => 'STATUS',
            ];

            foreach ($headers as $cell => $headerText) {
                $sheet->setCellValue($cell, $headerText);
            }

            // Style column headers: Yellow background, bold, centered, bordered
            $sheet->getStyle('A4:E4')->getFont()->setBold(true)->setSize(11)->setColor(new Color('000000'));
            $sheet->getStyle('A4:E4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A4:E4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A4:E4')->getAlignment()->setWrapText(true);
            $sheet->getStyle('A4:E4')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('FFFF00'); // Yellow background
            $sheet->getStyle('A4:E4')->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)
                ->setColor(new Color('000000'));

            // Set row height for headers
            $sheet->getRowDimension(4)->setRowHeight(30);

            // ==================== DATA ROWS ====================
            $dataRow = 5; // Start from row 5
            $itemNumber = 1;

            foreach ($items as $item) {
                // Column A: Item Number (centered)
                $sheet->setCellValue('A'.$dataRow, $itemNumber);
                $sheet->getStyle('A'.$dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A'.$dataRow)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

                // Column B: Task (priority_title)
                $sheet->setCellValue('B'.$dataRow, (string) ($item->priority_title ?? ''));
                $sheet->getStyle('B'.$dataRow)->getAlignment()->setWrapText(true);
                $sheet->getStyle('B'.$dataRow)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

                // Column C: Target Date (formatted as DD-MMM-YY)
                if ($item->target_deadline) {
                    $formattedDate = Carbon::parse($item->target_deadline)->format('d-M-y');
                    $sheet->setCellValue('C'.$dataRow, $formattedDate);
                } else {
                    $sheet->setCellValue('C'.$dataRow, '');
                }
                $sheet->getStyle('C'.$dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C'.$dataRow)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                $sheet->getStyle('C'.$dataRow)->getAlignment()->setWrapText(true);

                // Column D: Remarks (notes)
                $sheet->setCellValue('D'.$dataRow, (string) ($item->notes ?? ''));
                $sheet->getStyle('D'.$dataRow)->getAlignment()->setWrapText(true);
                $sheet->getStyle('D'.$dataRow)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

                // Column E: Status (uppercase)
                $rawStatus = trim((string) ($item->status ?? 'NOT STARTED'));
                $upper = strtoupper($rawStatus);
                // Normalize to required values
                if (preg_match('/NOT\s*STARTED/i', $rawStatus)) {
                    $statusText = 'NOT STARTED';
                } elseif (preg_match('/ACCOMPLISHED|COMPLETED|DONE/i', $rawStatus)) {
                    $statusText = 'ACCOMPLISHED';
                } elseif (preg_match('/ON\s*-?\s*PROGRESS|IN\s*PROGRESS|PROGRESS|PROCESSING/i', $rawStatus)) {
                    $statusText = 'ONPROGRESS';
                } else {
                    $statusText = $upper; // fallback uppercase
                }
                $sheet->setCellValue('E'.$dataRow, $statusText);
                $sheet->getStyle('E'.$dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('E'.$dataRow)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                $sheet->getStyle('E'.$dataRow)->getAlignment()->setWrapText(true);

                // Apply borders to all data cells
                $sheet->getStyle('A'.$dataRow.':E'.$dataRow)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->setColor(new Color('000000'));

                // Set row height with auto-sizing consideration
                $sheet->getRowDimension($dataRow)->setRowHeight(-1); // Auto height

                $itemNumber++;
                $dataRow++;
            }

            // Add extra empty rows for future entries (optional - makes report look complete)
            $emptyRowsToAdd = max(0, 12 - $items->count()); // Ensure at least 12 rows total
            for ($i = 0; $i < $emptyRowsToAdd; $i++) {
                $sheet->setCellValue('A'.$dataRow, $itemNumber);
                $sheet->getStyle('A'.$dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Apply borders
                $sheet->getStyle('A'.$dataRow.':E'.$dataRow)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->setColor(new Color('000000'));

                $itemNumber++;
                $dataRow++;
            }

            // ==================== COLUMN WIDTHS ====================
            // Fixed widths tuned to match the provided image while relying on Wrap Text
            $sheet->getColumnDimension('A')->setWidth(6.5);   // ITEM NO.
            $sheet->getColumnDimension('B')->setWidth(45);    // TASK
            $sheet->getColumnDimension('C')->setWidth(20);    // TARGET DATE TO COMPLETED
            $sheet->getColumnDimension('D')->setWidth(35);    // REMARKS
            $sheet->getColumnDimension('E')->setWidth(18);    // STATUS

            // Enable text wrapping for all data columns
            $lastRow = $dataRow - 1;
            $sheet->getStyle('A5:E'.$lastRow)->getAlignment()->setWrapText(true);
            // Ensure reasonable minimal row height for readability while allowing auto-expansion
            for ($r = 5; $r <= $lastRow; $r++) {
                $sheet->getRowDimension($r)->setRowHeight(-1);
            }

            // ==================== PAGE SETUP ====================
            // Set page orientation and margins for printing
            $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
            $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
            $sheet->getPageSetup()->setFitToWidth(1);
            $sheet->getPageSetup()->setFitToHeight(0);

            // Set print area
            $sheet->getPageSetup()->setPrintArea('A1:E'.$lastRow);

            // Set margins
            $sheet->getPageMargins()->setTop(0.5);
            $sheet->getPageMargins()->setRight(0.5);
            $sheet->getPageMargins()->setBottom(0.5);
            $sheet->getPageMargins()->setLeft(0.5);

            // ==================== CREATE AND RETURN FILE ====================
            // Create writer and save to temporary file
            $writer = new Xlsx($spreadsheet);

            // Create temporary file with proper error handling
            $tempFile = tempnam(sys_get_temp_dir(), 'task_priority_export_');
            if (! $tempFile) {
                throw new \Exception('Failed to create temporary file for export');
            }

            // Save the file with error handling
            try {
                $writer->save($tempFile);
            } catch (\Exception $saveException) {
                // Clean up the temporary file if save fails
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
                throw new \Exception('Failed to save Excel file: '.$saveException->getMessage());
            }

            // Verify file exists and is readable before download
            if (! file_exists($tempFile) || ! is_readable($tempFile)) {
                throw new \Exception('Generated file is not accessible for download');
            }

            // Return file download response
            return response()->download($tempFile, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Task Priority Export Failed', [
                'user_id' => auth()->id(),
                'task_priority_id' => $taskPriority->id,
                'group_key' => $taskPriority->group_key ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Return appropriate error response based on exception type
            if ($e instanceof \PhpOffice\PhpSpreadsheet\Exception) {
                return redirect()->back()->with('error', 'Excel generation failed. Please try again or contact support.');
            } elseif (strpos($e->getMessage(), 'memory') !== false) {
                return redirect()->back()->with('error', 'Export failed due to memory limitations. Please try with fewer items or contact support.');
            } elseif (strpos($e->getMessage(), 'permission') !== false) {
                return redirect()->back()->with('error', 'Export failed due to file permission issues. Please contact support.');
            } else {
                return redirect()->back()->with('error', 'Failed to export task priorities. Please try again or contact support if the problem persists.');
            }
        }
    }
}
