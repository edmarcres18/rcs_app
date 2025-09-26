<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Events\InstructionRepliedEvent;
use App\Models\Instruction;
use App\Models\InstructionActivity;
use App\Models\InstructionReply;
use App\Models\User;
use App\Notifications\InstructionAssigned;
use App\Notifications\InstructionForwarded;
use App\Notifications\InstructionReplied;
use App\Services\UserActivityService;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Notifications\InstructionForwardedToSender;

class InstructionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the instructions.
     */
    public function index()
    {
        $user = Auth::user();

        // SYSTEM_ADMIN can't send/receive instructions, only monitor
        if ($user->roles === UserRole::SYSTEM_ADMIN) {
            return redirect()->route('instructions.monitor');
        }

        // Helper function for recipient display logic
        $getRecipientDisplay = function ($recipients) {
            if ($recipients->isEmpty()) {
                return 'No Recipients';
            }

            // Check if all users with the same role are selected
            $allHaveSameRole = $recipients->pluck('roles')->unique()->count() === 1;
            $useGenericRecipient = false;

            if ($allHaveSameRole) {
                $role = $recipients->first()->roles;

                // Get all users in the system with this role (excluding SYSTEM_ADMIN)
                $allUsersWithRole = User::where('roles', $role)
                    ->where('roles', '!=', UserRole::SYSTEM_ADMIN->value)
                    ->get();

                // Check if all users with this role are selected
                $allRoleUsersSelected = $allUsersWithRole->count() === $recipients->count() &&
                    $allUsersWithRole->pluck('id')->sort()->values()->toArray() ===
                    $recipients->pluck('id')->sort()->values()->toArray();

                if ($allRoleUsersSelected) {
                    switch ($role) {
                        case UserRole::EMPLOYEE:
                            return 'ALL EMPLOYEES';
                        case UserRole::SUPERVISOR:
                            return 'ALL SUPERVISORS';
                        case UserRole::ADMIN:
                            return 'ALL ADMINS';
                    }
                }
            }

            // If not all users of a role are selected, show individual names
            return $recipients->map(function ($recipient) {
                if ($recipient->roles === UserRole::EMPLOYEE) {
                    // For employees, show first name only
                    return $recipient->first_name;
                }

                if ($recipient->roles === UserRole::SUPERVISOR || $recipient->roles === UserRole::ADMIN) {
                    // For supervisors and admins, show first name + last name initial
                    $firstName = $recipient->first_name;
                    $lastName = $recipient->last_name;

                    if (!empty($lastName)) {
                        $lastInitial = mb_strtoupper(mb_substr(trim($lastName), 0, 1));
                        return $firstName . ' ' . $lastInitial . '.';
                    }

                    return $firstName;
                }

                // Fallback for any other role
                return $recipient->full_name;
            })->implode(', ');
        };

        // Get received instructions with accurate counts and scoped pivot for the current user
        $receivedInstructions = Instruction::whereHas('recipients', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->with([
                'sender',
                // Scope recipients to the authenticated user so we get the correct pivot row
                'recipients' => function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                },
            ])
            ->withCount([
                'replies as replies_count',
                // Count replies that have an attachment stored
                'replies as attachments_count' => function ($q) {
                    $q->whereNotNull('attachment_path');
                },
            ])
            ->latest('instructions.created_at')
            ->get();

        // Map forwarder names in one query to avoid N+1
        $forwarderIds = $receivedInstructions->map(function ($instruction) use ($user) {
            $pivot = optional($instruction->recipients->first())->pivot;
            return $pivot?->forwarded_by_id;
        })->filter()->unique()->values();

        $forwarders = User::whereIn('id', $forwarderIds)->get()->keyBy('id');

        $receivedInstructions->each(function ($instruction) use ($getRecipientDisplay, $forwarders) {
            $instruction->recipientDisplay = $getRecipientDisplay($instruction->recipients);
            $pivot = optional($instruction->recipients->first())->pivot;
            $instruction->pivot = (object) [
                'is_read' => (bool) ($pivot->is_read ?? false),
                'forwarded_by_id' => $pivot->forwarded_by_id ?? null,
            ];
            $instruction->forwarded_by_user = $pivot?->forwarded_by_id ? $forwarders->get($pivot->forwarded_by_id) : null;
        });

        // Get sent instructions with counts
        $sentInstructions = Instruction::where('sender_id', $user->id)
            ->with('recipients')
            ->withCount([
                'replies as replies_count',
                'replies as attachments_count' => function ($q) {
                    $q->whereNotNull('attachment_path');
                },
            ])
            ->latest()
            ->get();

        $sentInstructions->each(function ($instruction) use ($getRecipientDisplay) {
            $instruction->recipientDisplay = $getRecipientDisplay($instruction->recipients);
        });

        return view('instructions.index', compact('receivedInstructions', 'sentInstructions'));
    }

    /**
     * Show the form for creating a new instruction.
     */
    public function create()
    {
        $user = Auth::user();

        // SYSTEM_ADMIN can't send instructions
        if ($user->roles === UserRole::SYSTEM_ADMIN) {
            return redirect()->route('instructions.monitor')
                ->with('error', 'System Administrators cannot send instructions.');
        }

        // Get potential recipients (exclude self and SYSTEM_ADMIN users)
        $potentialRecipients = User::where('id', '!=', $user->id)
            ->where('roles', '!=', UserRole::SYSTEM_ADMIN->value)
            ->orderBy('first_name')
            ->get();

        return view('instructions.create', compact('potentialRecipients'));
    }

    /**
     * Store a newly created instruction in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // SYSTEM_ADMIN can't send instructions
        if ($user->roles === UserRole::SYSTEM_ADMIN) {
            return redirect()->route('instructions.monitor')
                ->with('error', 'System Administrators cannot send instructions.');
        }

        // Validate basic fields
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'recipient_type' => 'required|string|in:specific,role,all',
            'target_deadline' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        // Get recipients based on selection mode
        $recipientIds = [];

        if ($request->recipient_type === 'specific') {
            // Validate specific users are selected
            $validator = Validator::make($request->all(), [
                'recipients' => 'required|array|min:1',
                'recipients.*' => 'exists:users,id'
            ]);

            if ($validator->fails()) {
                return back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $recipientIds = $request->recipients;
        }
        elseif ($request->recipient_type === 'role') {
            // Validate roles are selected
            $validator = Validator::make($request->all(), [
                'selected_roles' => 'required|array|min:1',
                'selected_roles.*' => 'string'
            ]);

            if ($validator->fails()) {
                return back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Get all users with the selected roles
            $recipientIds = User::whereIn('roles', $request->selected_roles)
                ->where('id', '!=', $user->id)
                ->pluck('id')
                ->toArray();

            if (empty($recipientIds)) {
                return back()
                    ->withErrors(['recipients' => 'No users found with the selected roles.'])
                    ->withInput();
            }
        }
        elseif ($request->recipient_type === 'all') {
            // Get all users except current user and SYSTEM_ADMIN
            $recipientIds = User::where('id', '!=', $user->id)
                ->where('roles', '!=', UserRole::SYSTEM_ADMIN->value)
                ->pluck('id')
                ->toArray();

            if (empty($recipientIds)) {
                return back()
                    ->withErrors(['recipients' => 'No eligible recipients found in the system.'])
                    ->withInput();
            }
        }

        // Validate recipient list doesn't include self
        if (in_array($user->id, $recipientIds)) {
            return back()
                ->withErrors(['recipients' => 'You cannot send an instruction to yourself.'])
                ->withInput();
        }

        // Verify recipients aren't SYSTEM_ADMIN
        $recipientUsers = User::whereIn('id', $recipientIds)->get();
        foreach ($recipientUsers as $recipient) {
            if ($recipient->roles === UserRole::SYSTEM_ADMIN) {
                return back()
                    ->withErrors(['recipients' => 'System Administrators cannot receive instructions.'])
                    ->withInput();
            }
        }

        DB::beginTransaction();
        try {
            // Create the instruction
            $instruction = Instruction::create([
                'sender_id' => $user->id,
                'title' => $request->title,
                'body' => $request->body,
                'target_deadline' => $request->target_deadline,
            ]);

            // Attach recipients
            $instruction->recipients()->attach($recipientIds, ['is_read' => false]);

            // Log the activity
            InstructionActivity::create([
                'instruction_id' => $instruction->id,
                'user_id' => $user->id,
                'action' => 'sent'
            ]);

            // Send notifications and email to all recipients
            Notification::send($recipientUsers, new InstructionAssigned($instruction));

            // Log system activity
            UserActivityService::log(
                'instruction_created',
                'Created a new instruction: ' . $request->title,
                [
                    'instruction_id' => $instruction->id,
                    'recipient_count' => count($recipientIds),
                    'recipient_type' => $request->recipient_type
                ]
            );

            DB::commit();
            return redirect()->route('instructions.show', $instruction)
                ->with('success', 'Instruction sent successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Failed to create instruction: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified instruction.
     */
    public function show(Instruction $instruction)
    {
        $user = Auth::user();

        // Check if user has access to this instruction
        if (!$instruction->canBeAccessedBy($user)) {
            return response()->view('errors.403', ['message' => 'You do not have permission to view this instruction.'], 403);
        }

        // Load instruction with all necessary relationships
        $instruction->load(['sender', 'recipients']);

        // If the user is a recipient and hasn't read it yet, mark as read
        $recipient = $instruction->recipients()->where('user_id', $user->id)->first();
        if ($recipient && isset($recipient->pivot) && !$recipient->pivot->is_read) {
            $this->markAsRead($instruction);
        }

        // Generate recipient display string
        $recipients = $instruction->recipients;
        $recipientDisplay = '';

        if ($recipients->isNotEmpty()) {
            // Check if all users with the same role are selected
            $allHaveSameRole = $recipients->pluck('roles')->unique()->count() === 1;
            $useGenericRecipient = false;

            if ($allHaveSameRole) {
                $role = $recipients->first()->roles;

                // Get all users in the system with this role (excluding SYSTEM_ADMIN)
                $allUsersWithRole = User::where('roles', $role)
                    ->where('roles', '!=', UserRole::SYSTEM_ADMIN->value)
                    ->get();

                // Check if all users with this role are selected
                $allRoleUsersSelected = $allUsersWithRole->count() === $recipients->count() &&
                    $allUsersWithRole->pluck('id')->sort()->values()->toArray() ===
                    $recipients->pluck('id')->sort()->values()->toArray();

                if ($allRoleUsersSelected) {
                    switch ($role) {
                        case UserRole::EMPLOYEE:
                            $recipientDisplay = 'ALL EMPLOYEES';
                            $useGenericRecipient = true;
                            break;
                        case UserRole::SUPERVISOR:
                            $recipientDisplay = 'ALL SUPERVISORS';
                            $useGenericRecipient = true;
                            break;
                        case UserRole::ADMIN:
                            $recipientDisplay = 'ALL ADMINS';
                            $useGenericRecipient = true;
                            break;
                    }
                }
            }

            // If not all users of a role are selected, show individual names
            if (!$useGenericRecipient) {
                $recipientNames = $recipients->map(function ($recipient) {
                    if ($recipient->roles === UserRole::EMPLOYEE) {
                        // For employees, show first name only
                        return $recipient->first_name;
                    }

                    if ($recipient->roles === UserRole::SUPERVISOR || $recipient->roles === UserRole::ADMIN) {
                        // For supervisors and admins, show first name + last name initial
                        $firstName = $recipient->first_name;
                        $lastName = $recipient->last_name;

                        if (!empty($lastName)) {
                            $lastInitial = mb_strtoupper(mb_substr(trim($lastName), 0, 1));
                            return $firstName . ' ' . $lastInitial . '.';
                        }

                        return $firstName;
                    }

                    // Fallback for any other role
                    return $recipient->full_name;
                });

                $recipientDisplay = $recipientNames->implode(', ');
            }
        }


        // Get all activities and replies
        $activities = $instruction->activities()
            ->with(['user', 'targetUser'])
            ->orderBy('created_at')
            ->get();

        $replies = $instruction->replies()
            ->with('user')
            ->orderBy('created_at')
            ->get();

        return view('instructions.show', compact('instruction', 'activities', 'replies', 'recipientDisplay'));
    }

    /**
     * Mark an instruction as read.
     */
    public function markAsRead(Instruction $instruction)
    {
        $user = Auth::user();

        // Check if user is a recipient
        $recipientExists = $instruction->recipients()
            ->where('user_id', $user->id)
            ->exists();

        if (!$recipientExists) {
            return back()->with('error', 'You are not a recipient of this instruction.');
        }

        // Update the pivot table
        $instruction->recipients()->updateExistingPivot($user->id, ['is_read' => true]);

        // Log the activity
        InstructionActivity::create([
            'instruction_id' => $instruction->id,
            'user_id' => $user->id,
            'action' => 'read'
        ]);

        UserActivityService::log(
            'instruction_read',
            'Read instruction: ' . $instruction->title,
            ['instruction_id' => $instruction->id]
        );

        return back()->with('success', 'Instruction marked as read.');
    }

    /**
     * Add a reply to an instruction.
     */
    public function reply(Request $request, Instruction $instruction)
    {
        $user = Auth::user();

        // Check if user has access to this instruction, handle AJAX appropriately
        if (!$instruction->canBeAccessedBy($user)) {
            $message = 'You do not have permission to reply to this instruction.';
            if ($request->ajax()) {
                return response()->json(['message' => $message], 403);
            }
            return response()->view('errors.403', ['message' => $message], 403);
        }

        // System admin can't reply, handle AJAX appropriately
        if ($user->roles === UserRole::SYSTEM_ADMIN) {
            $message = 'System Administrators cannot reply to instructions.';
            if ($request->ajax()) {
                return response()->json(['message' => $message], 403);
            }
            return redirect()->route('instructions.show', $instruction)
                ->with('error', $message);
        }

        // Validation rules for reply content and file attachment
        $rules = [
            'content' => 'required|string',
        ];

        // Add file validation rules if file is uploaded
        if ($request->hasFile('attachment')) {
            $rules['attachment'] = [
                'file',
                'max:25600', // 25MB in KB
                'mimes:jpg,jpeg,png,gif,bmp,webp,svg,tiff,ico,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,rtf,odt,ods,odp,zip,rar,7z,csv,json,xml'
            ];
        }

        $validator = Validator::make($request->all(), $rules);

        // If validation fails, return JSON error for AJAX requests
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            // Prepare reply data
            $replyData = [
                'instruction_id' => $instruction->id,
                'user_id' => $user->id,
                'content' => $request->content,
            ];

            // Handle file upload if present
            if ($request->hasFile('attachment')) {
                try {
                    $fileUploadService = new FileUploadService();
                    $fileInfo = $fileUploadService->uploadFile($request->file('attachment'));

                    $replyData['attachment_filename'] = $fileInfo['filename'];
                    $replyData['attachment_original_name'] = $fileInfo['original_name'];
                    $replyData['attachment_path'] = $fileInfo['path'];
                    $replyData['attachment_mime_type'] = $fileInfo['mime_type'];
                    $replyData['attachment_size'] = $fileInfo['size'];
                } catch (\Exception $e) {
                    DB::rollBack();

                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'File upload failed: ' . $e->getMessage()
                        ], 422);
                    }

                    return back()
                        ->with('error', 'File upload failed: ' . $e->getMessage())
                        ->withInput();
                }
            }

            // Create the reply
            $reply = InstructionReply::create($replyData);

            // Log the activity
            InstructionActivity::create([
                'instruction_id' => $instruction->id,
                'user_id' => $user->id,
                'action' => 'replied',
                'content' => $request->content
            ]);

            // Notify sender if the replier is not the sender
            if ($user->id !== $instruction->sender_id) {
                $instruction->sender->notify(new InstructionReplied($instruction, $user, $reply));
            }

            // Notify all other recipients
            $recipients = $instruction->recipients()
                            ->where('user_id', '!=', $user->id)
                            ->get();

            foreach ($recipients as $recipient) {
                $recipient->notify(new InstructionReplied($instruction, $user, $reply));
            }

            // Log system activity
            UserActivityService::log(
                'instruction_replied',
                'Replied to instruction: ' . $instruction->title,
                ['instruction_id' => $instruction->id]
            );

            // Broadcast the new reply for real-time updates
            event(new InstructionRepliedEvent($instruction, $reply, $user));

            DB::commit();

            // For AJAX requests, return a JSON response with all needed data
            if ($request->ajax()) {
                $replyResponse = [
                    'id' => $reply->id,
                    'content' => $reply->content,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->full_name,
                        'avatar_url' => $user->avatar_url
                    ],
                    'created_at' => $reply->created_at->toISOString(),
                    'attachment' => null
                ];

                // Add attachment information if present
                if ($reply->hasAttachment()) {
                    $replyResponse['attachment'] = [
                        'url' => $reply->attachment_url,
                        'original_name' => $reply->attachment_original_name,
                        'size' => $reply->formatted_file_size,
                        'mime_type' => $reply->attachment_mime_type,
                        'is_image' => $reply->isAttachmentImage(),
                        'icon' => $reply->attachment_icon
                    ];
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Reply added successfully.',
                    'reply' => $replyResponse
                ]);
            }

            return redirect()->route('instructions.show', $instruction)
                ->with('success', 'Reply added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            // For AJAX requests, return error as JSON
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add reply: ' . $e->getMessage()
                ], 500);
            }

            return back()
                ->with('error', 'Failed to add reply: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for forwarding an instruction.
     */
    public function showForward(Instruction $instruction)
    {
        $user = Auth::user();

        // Check if user has access to this instruction
        if (!$instruction->canBeAccessedBy($user)) {
            return response()->view('errors.403', ['message' => 'You do not have permission to forward this instruction.'], 403);
        }

        // Check if user is eligible to forward
        if ($user->roles === UserRole::SYSTEM_ADMIN) {
            return redirect()->route('instructions.show', $instruction)
                ->with('error', 'System Administrators cannot forward instructions.');
        }

        // Get potential recipients (exclude self, sender, existing recipients, and SYSTEM_ADMIN users)
        $existingRecipientIds = $instruction->recipients()->pluck('users.id')->toArray();
        $potentialRecipients = User::where('id', '!=', $user->id)
            ->where('id', '!=', $instruction->sender_id)
            ->whereNotIn('id', $existingRecipientIds)
            ->where('roles', '!=', UserRole::SYSTEM_ADMIN->value)
            ->orderBy('first_name')
            ->get();

        return view('instructions.forward', compact('instruction', 'potentialRecipients'));
    }

    /**
     * Forward an instruction to additional users.
     */
    public function forward(Request $request, Instruction $instruction)
    {
        $user = Auth::user();

        // Check if user has access to this instruction
        if (!$instruction->canBeAccessedBy($user)) {
            return response()->view('errors.403', ['message' => 'You do not have permission to forward this instruction.'], 403);
        }

        // Check if user is eligible to forward
        if ($user->roles === UserRole::SYSTEM_ADMIN) {
            return redirect()->route('instructions.show', $instruction)
                ->with('error', 'System Administrators cannot forward instructions.');
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'forward_message' => 'nullable|string',
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'exists:users,id'
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if any of the recipients are already assigned
        $existingRecipients = $instruction->recipients()->whereIn('users.id', $request->recipients)->pluck('users.id')->toArray();
        if (!empty($existingRecipients)) {
            $existingUsers = User::whereIn('id', $existingRecipients)->pluck('first_name', 'last_name')->toArray();
            $errorMsg = 'Some users are already recipients: ' . implode(', ', $existingUsers);
            return back()->withErrors(['recipients' => $errorMsg])->withInput();
        }

        // Verify recipients aren't SYSTEM_ADMIN
        $recipientUsers = User::whereIn('id', $request->recipients)->get();
        foreach ($recipientUsers as $recipient) {
            if ($recipient->roles === UserRole::SYSTEM_ADMIN) {
                return back()
                    ->withErrors(['recipients' => 'System Administrators cannot receive instructions.'])
                    ->withInput();
            }
        }

        DB::beginTransaction();
        try {
            // Attach new recipients with forwarded_by_id
            $pivotData = collect($request->recipients)->mapWithKeys(function ($id) use ($user) {
                return [$id => ['is_read' => false, 'forwarded_by_id' => $user->id]];
            })->all();

            $instruction->recipients()->attach($pivotData);

            // Log the activity
            InstructionActivity::create([
                'instruction_id' => $instruction->id,
                'user_id' => $user->id,
                'action' => 'forwarded',
                'content' => $request->forward_message,
            ]);

            // Send notifications and email to all new recipients
            Notification::send($recipientUsers, new InstructionForwarded($instruction, $user, $request->forward_message));

            // Notify the original sender that the instruction was forwarded
            if ($instruction->sender_id !== $user->id) {
                $instruction->sender->notify(new InstructionForwardedToSender($instruction, $user, $recipientUsers));
            }

            // Log system activity
            UserActivityService::log(
                'instruction_forwarded',
                'Forwarded instruction: ' . $instruction->title,
                [
                    'instruction_id' => $instruction->id,
                    'recipient_count' => count($request->recipients)
                ]
            );

            DB::commit();
            return redirect()->route('instructions.show', $instruction)
                ->with('success', 'Instruction forwarded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Failed to forward instruction: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Get updates for an instruction for real-time functionality.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Instruction $instruction
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUpdates(Request $request, Instruction $instruction)
    {
        $user = Auth::user();

        // Check if user has access to this instruction
        if (!$instruction->canBeAccessedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view this instruction.'
            ], 403);
        }

        $lastReplyId = $request->input('last_reply_id', 0);
        $lastActivityId = $request->input('last_activity_id', 0);

        // Get new replies
        $newReplies = $instruction->replies()
            ->with('user')
            ->where('id', '>', $lastReplyId)
            ->orderBy('created_at')
            ->get()
            ->map(function($reply) {
                $replyData = [
                    'id' => $reply->id,
                    'content' => $reply->content,
                    'user' => [
                        'id' => $reply->user->id,
                        'name' => $reply->user->full_name
                    ],
                    'created_at' => $reply->created_at->format('M d, Y g:i A'),
                    'attachment' => null
                ];

                // Add attachment information if present
                if ($reply->hasAttachment()) {
                    $replyData['attachment'] = [
                        'url' => $reply->attachment_url,
                        'original_name' => $reply->attachment_original_name,
                        'size' => $reply->formatted_file_size,
                        'mime_type' => $reply->attachment_mime_type,
                        'is_image' => $reply->isAttachmentImage(),
                        'icon' => $reply->attachment_icon
                    ];
                }

                return $replyData;
            });

        // Get new activities
        $newActivities = $instruction->activities()
            ->with(['user', 'targetUser'])
            ->where('id', '>', $lastActivityId)
            ->orderBy('created_at')
            ->get()
            ->map(function($activity) {
                return [
                    'id' => $activity->id,
                    'action' => $activity->action,
                    'content' => $activity->content,
                    'user' => [
                        'id' => $activity->user->id,
                        'name' => $activity->user->full_name
                    ],
                    'target_user' => $activity->targetUser ? [
                        'id' => $activity->targetUser->id,
                        'name' => $activity->targetUser->full_name
                    ] : null,
                    'created_at' => $activity->created_at->format('M d, Y g:i A')
                ];
            });

        return response()->json([
            'success' => true,
            'updates' => [
                'replies' => $newReplies,
                'activities' => $newActivities
            ]
        ]);
    }

    /**
     * Download attachment from instruction reply
     */
    public function downloadAttachment(InstructionReply $reply)
    {
        $user = Auth::user();

        // Check if user has access to the instruction
        if (!$reply->instruction->canBeAccessedBy($user)) {
            abort(403, 'You do not have permission to access this attachment.');
        }

        // Check if reply has attachment
        if (!$reply->hasAttachment()) {
            abort(404, 'Attachment not found.');
        }

        // Check if file exists
        if (!Storage::disk('public')->exists($reply->attachment_path)) {
            abort(404, 'File not found on server.');
        }

        // Log the download activity
        UserActivityService::log(
            'attachment_downloaded',
            'Downloaded attachment: ' . $reply->attachment_original_name,
            ['reply_id' => $reply->id, 'instruction_id' => $reply->instruction_id]
        );

        // Return file download response
        return Storage::disk('public')->download(
            $reply->attachment_path,
            $reply->attachment_original_name
        );
    }
}
