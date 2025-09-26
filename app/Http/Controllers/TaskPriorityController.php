<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskPriorityStoreRequest;
use App\Http\Requests\TaskPriorityUpdateRequest;
use App\Http\Requests\TaskPriorityGroupUpdateRequest;
use App\Models\Instruction;
use App\Models\TaskPriority;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

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
        $isSender = (int)($taskPriority->instruction->sender_id ?? 0) === (int)$user->id;
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

        if ($request->filled('instruction_title')) {
            $baseQuery->whereHas('instruction', function ($q) use ($request) {
                $q->where('title', 'like', '%'.$request->instruction_title.'%');
            });
        }

        $perPage = 15;
        $page = (int) ($request->input('page', 1));

        $groupKeysQuery = (clone $baseQuery)->select('group_key')->distinct();
        $totalGroups = (clone $groupKeysQuery)->count('group_key');
        $groupKeys = (clone $groupKeysQuery)
            ->orderBy('group_key')
            ->forPage($page, $perPage)
            ->pluck('group_key');

        $representatives = TaskPriority::with(['instruction', 'sender'])
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
            ->with('success', $deleted . ' task priorities permanently deleted.');
    }

    /**
     * Export the specified group's items as a styled Excel worksheet (HTML-based .xls).
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

        $fileName = 'task-priority-group-'.$groupKey.'-'.now()->format('Ymd_His').'.xls';

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ];

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

        return response()->stream(function () use ($items, $preparedBy, $generatedAt) {
            // Output UTF-8 BOM
            echo chr(0xEF).chr(0xBB).chr(0xBF);
            // Start HTML table with minimal styling Excel understands
            echo '<html><head><meta charset="UTF-8"><style>
                /* Page watermark: repeated SVG saying RCS */
                body {
                    /* Inline SVG background watermark */
                    background-image: url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHdpZHRoPScyMDAiIGhlaWdodD0nMjAwJz48ZGVmcz48ZmlsdGVyIGlkPSdzJyB4PSIwIiB5PSIwIj48ZmVGbG9vZEZpbHRlci8+PC9maWx0ZXI+PC9kZWZzPjxyZWN0IHdpZHRoPScyMDAnIGhlaWdodD0nMjAwJyBmaWxsPSJ3aGl0ZSIgZmlsbC1vcGFjaXR5PSIwIi8+PHRleHQgeD0nMTAwJyB5PScxMDAnIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZvbnQtZmFtaWx5PSJTZWdvZSBVSSwgQXJpYWwsIHNhbnMtc2VyaWYiIGZvbnQtc2l6ZT0nNjBweCcgZmlsbD0nI0ZGRicgZmlsbC1vcGFjaXR5PScwLjA4JyB0cmFuc2Zvcm09InJvdGF0ZSgtMzAgMTAwIDEwMCkiPlJDUzwvdGV4dD48L3N2Zz4=");
                    background-repeat: repeat;
                    background-position: 0 0;
                }
                table { border-collapse: collapse; width: 100%; font-family: Segoe UI, Arial, sans-serif; }
                th { background:#0ea5e9; color:#fff; text-transform:uppercase; font-size:12px; letter-spacing:.04em; }
                th, td { border:1px solid #d1d5db; padding:8px 10px; }
                tr:nth-child(even) td { background:#f9fafb; }
                .title { font-weight:700; }
                .status { font-weight:700; }
                .status-accomplished { background:#dcfce7; color:#166534; }
                .status-processing { background:#dbeafe; color:#1e40af; }
                .status-not-started { background:#e5e7eb; color:#374151; }
                .meta { margin-bottom:10px; }
                .meta td { border:none; padding:2px 0; }
            </style></head><body>';
            echo '<table class="meta"><tr><td><strong>Prepared By:</strong> '.e($preparedBy).'</td></tr>';
            echo '<tr><td><strong>Generated At:</strong> '.e($generatedAt).'</td></tr></table>';
            echo '<table><thead><tr>
                    <th>Priority Title</th>
                    <th>Status</th>
                    <th>Target Deadline</th>
                    <th>Notes</th>
                </tr></thead><tbody>';
            foreach ($items as $i) {
                $statusSlug = \Illuminate\Support\Str::slug($i->status);
                $deadline = optional($i->target_deadline)->format('Y-m-d');
                $notes = e($i->notes);
                $title = e($i->priority_title);
                $status = e($i->status);
                echo '<tr>';
                echo '<td class="title">'.$title.'</td>';
                echo '<td class="status status-'.$statusSlug.'">'.$status.'</td>';
                echo '<td>'.$deadline.'</td>';
                echo '<td>'.$notes.'</td>';
                echo '</tr>';
            }
            echo '</tbody></table></body></html>';
        }, 200, $headers);
    }
}
