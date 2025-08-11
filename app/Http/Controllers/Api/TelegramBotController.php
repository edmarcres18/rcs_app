<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Instruction;
use App\Models\InstructionActivity;
use App\Models\InstructionReply;
use App\Models\User;
use App\Models\UserActivity;
use App\Notifications\InstructionReplied as InstructionRepliedNotification;
use App\Events\InstructionRepliedEvent;
use App\Services\UserActivityService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Jobs\ProcessTelegramUpdate;

class TelegramBotController extends Controller
{
    /**
     * The Telegram service instance.
     *
     * @var \App\Services\TelegramService
     */
    protected $telegramService;

    /**
     * Create a new controller instance.
     *
     * @param  \App\Services\TelegramService  $telegramService
     * @return void
     */
    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Handle the incoming Telegram webhook request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function webhook(Request $request)
    {
        try {
            $update = $request->all();
            Log::info('Telegram webhook received', ['update' => $update]);

            // Queue after the HTTP response is sent to avoid any DB/queue latency blocking Telegram
            ProcessTelegramUpdate::dispatch($update)->afterResponse();
        } catch (\Throwable $e) {
            // Never fail the webhook response; log and still return 200
            Log::error('Telegram webhook handling error', [
                'message' => $e->getMessage(),
            ]);
        }

        // Telegram expects a 200 OK quickly to consider delivery successful
        // Keep payload aligned with test expectations
        return response()->json(['status' => 'success']);
    }

    /**
     * Process an update from Telegram.
     *
     * @param array $update
     * @return void
     */
    public function processUpdate(array $update)
    {
        // Check if this is a message with text
        if (isset($update['message']) && isset($update['message']['text'])) {
            $message = $update['message'];
            $chatId = $message['chat']['id'];
            $text = $message['text'];
            $username = $message['from']['username'] ?? null;

            // Save the chat data for debugging
            Log::info('Processing Telegram message', [
                'chat_id' => $chatId,
                'username' => $username,
                'text' => $text
            ]);

            // Check if this is a command (starts with /)
            if (strpos($text, '/') === 0) {
                $this->handleCommand($text, $chatId, $username, $message);
            }
        }
    }

    /**
     * Handle Telegram bot commands.
     *
     * @param string $text
     * @param string $chatId
     * @param string|null $username
     * @param array $message
     * @return void
     */
    protected function handleCommand($text, $chatId, $username = null, $message = [])
    {
        // Extract command and parameters
        $parts = explode(' ', trim($text));
        $command = strtolower(array_shift($parts));
        $params = $parts;

        switch ($command) {
            case '/start':
                $this->handleStartCommand($chatId, $username);
                break;

            case '/link':
                $this->handleLinkCommand($chatId, $username, $params);
                break;

            case '/unlink':
                $this->handleUnlinkCommand($chatId, $username);
                break;

            case '/enable':
                $this->handleEnableNotificationsCommand($chatId, $username);
                break;

            case '/disable':
                $this->handleDisableNotificationsCommand($chatId, $username);
                break;

            case '/register':
                $this->handleRegisterCommand($chatId, $username, $params, $message);
                break;

            case '/help':
                $this->handleHelpCommand($chatId);
                break;

            case '/status':
                $this->handleStatusCommand($chatId, $username);
                break;

            case '/activity':
                $this->handleActivityCommand($chatId, $username);
                break;

            case '/pendings':
                $this->handlePendingsCommand($chatId, $username);
                break;

            case '/reply':
                $this->handleReplyCommand($chatId, $username, $params, $message);
                break;

            default:
                // Use predefined commands from config or fall back to default message
                if (!$this->telegramService->handleCommand($text, $chatId)) {
                    $this->telegramService->sendMessage(
                        $chatId,
                        "Unknown command. Type /help to see available commands."
                    );
                }
                break;
        }
    }

    /**
     * Handle the /start command.
     *
     * @param string $chatId
     * @param string|null $username
     * @return void
     */
    protected function handleStartCommand($chatId, $username = null)
    {
        $user = $this->findUserByTelegram($chatId, $username);

        if ($user) {
            // User is already linked
            $status = $user->telegram_notifications_enabled ? '✅ Enabled' : '❌ Disabled';
            $message = "<b>Welcome back, " . e($user->full_name) . "!</b>\n\n";
            $message .= "Your Telegram account is linked to the MHR Reporting Compliance System.\n\n";
            $message .= "<b>Email:</b> " . e($user->email) . "\n";
            $message .= "<b>Notification Status:</b> " . $status . "\n\n";
            $message .= "You are all set to receive real-time notifications. Type /help to see all available commands.";
        } else {
            // New user
            $message = "<b>Welcome to the MHR Reporting Compliance System Bot!</b>\n\n";
            $message .= "This bot delivers real-time notifications for instructions and deadlines directly to your Telegram account.\n\n";
            $message .= "To get started, please link this Telegram account with your MHR system email address using the following command:\n\n";
            $message .= "<code>/link your.email@example.com</code>\n\n";
            $message .= "<i>(Replace <b>your.email@example.com</b> with the email you use for the MHR system.)</i>";
        }

        $this->telegramService->sendMessage($chatId, $message);
    }

    /**
     * Handle the /link command.
     *
     * @param string $chatId
     * @param string|null $username
     * @param array $params
     * @return void
     */
    protected function handleLinkCommand($chatId, $username = null, $params = [])
    {
        // Check if an email parameter was provided
        $email = $params[0] ?? null;

        if (empty($email)) {
            $message = "Please provide your email address to link your account.\n";
            $message .= "Example: /link your.email@example.com";
            $this->telegramService->sendMessage($chatId, $message);
            return;
        }

        // Find user by email
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->telegramService->sendMessage($chatId, "No user found with email: {$email}");
            return;
        }

        // Update user with Telegram info
        $user->telegram_chat_id = $chatId;

        if ($username) {
            $user->telegram_username = $username;
        }

        // Enable Telegram notifications by default when linking
        $user->telegram_notifications_enabled = true;
        $user->save();

        $this->telegramService->sendMessage(
            $chatId,
            "Success! Your MHR Reporting Compliance System account has been linked to this Telegram account. You will now receive notifications here."
        );
    }

    /**
     * Handle the /unlink command.
     *
     * @param string $chatId
     * @param string|null $username
     * @return void
     */
    protected function handleUnlinkCommand($chatId, $username = null)
    {
        $user = $this->findUserByTelegram($chatId, $username);

        if (!$user) {
            $this->telegramService->sendMessage($chatId, "🚫 This Telegram account is not linked. Use /link to get started.");
            return;
        }

        // Restrict /unlink to SYSTEM_ADMIN only
        if ($user->roles !== UserRole::SYSTEM_ADMIN) {
            $this->telegramService->sendMessage($chatId, "🚫 Access denied. Only System Administrators can execute /unlink.");
            return;
        }

        $user->telegram_chat_id = null;
        $user->telegram_username = null;
        $user->telegram_notifications_enabled = false;
        $user->save();

        $this->telegramService->sendMessage($chatId, "Your Telegram account has been successfully unlinked from the MHR system.");
    }

    /**
     * Handle the /enable command.
     *
     * @param string $chatId
     * @param string|null $username
     * @return void
     */
    protected function handleEnableNotificationsCommand($chatId, $username = null)
    {
        $user = $this->findUserByTelegram($chatId, $username);

        if ($user) {
            if ($user->telegram_notifications_enabled) {
                $this->telegramService->sendMessage($chatId, "Notifications are already enabled for your account.");
            } else {
                $user->telegram_notifications_enabled = true;
                $user->save();
                $this->telegramService->sendMessage($chatId, "✅ Notifications have been enabled. You will now receive alerts via Telegram.");
            }
        } else {
            $this->telegramService->sendMessage($chatId, "This Telegram account is not linked. Use /link to get started.");
        }
    }

    /**
     * Handle the /disable command.
     *
     * @param string $chatId
     * @param string|null $username
     * @return void
     */
    protected function handleDisableNotificationsCommand($chatId, $username = null)
    {
        $user = $this->findUserByTelegram($chatId, $username);

        if ($user) {
            if (!$user->telegram_notifications_enabled) {
                $this->telegramService->sendMessage($chatId, "Notifications are already disabled for your account.");
            } else {
                $user->telegram_notifications_enabled = false;
                $user->save();
                $this->telegramService->sendMessage($chatId, "❌ Notifications have been disabled. You will no longer receive alerts via Telegram.");
            }
        } else {
            $this->telegramService->sendMessage($chatId, "This Telegram account is not linked. Use /link to get started.");
        }
    }

    /**
     * Handle the /register command.
     *
     * @param string $chatId
     * @param string|null $username
     * @param array $params
     * @param array $message
     * @return void
     */
    protected function handleRegisterCommand($chatId, $username = null, $params = [], $message = [])
    {
        // This is a placeholder - actual registration would require more complex flow with email verification
        $this->telegramService->sendMessage(
            $chatId,
            "Registration via Telegram is not available. Please register on our website first, then link your account using /link command."
        );
    }

    /**
     * Handle the /help command.
     *
     * @param string $chatId
     * @return void
     */
    protected function handleHelpCommand($chatId)
    {
        $message = "<b>MHR Reporting Compliance System Bot Help</b>\n\n";
        $message .= "Here are the available commands:\n\n";
        $message .= "<b>/start</b> - <i>Display welcome message and status.</i>\n";
        $message .= "<b>/link [email]</b> - <i>Link your Telegram to your MHR account.</i>\n";
        $message .= "<b>/unlink</b> - <i>Remove the link between your Telegram and MHR account.</i>\n";
        $message .= "<b>/status</b> - <i>Check your account linking status.</i>\n";
        $message .= "<b>/enable</b> - <i>Enable receiving notifications.</i>\n";
        $message .= "<b>/disable</b> - <i>Disable receiving notifications.</i>\n";
        $message .= "<b>/activity</b> - <i>Show your recent activities.</i>\n";
        $message .= "<b>/pendings</b> - <i>Show your pending instructions (unread or not replied).</i>\n";
        $message .= "<b>/reply [instruction_id] [message]</b> - <i>Reply to an instruction from Telegram.</i>\n";
        $message .= "<b>/help</b> - <i>Show this help message.</i>";

        $this->telegramService->sendMessage($chatId, $message);
    }

    /**
     * Handle the /status command.
     *
     * @param string $chatId
     * @param string|null $username
     * @return void
     */
    protected function handleStatusCommand($chatId, $username = null)
    {
        $user = $this->findUserByTelegram($chatId, $username);

        if ($user) {
            $status = $user->telegram_notifications_enabled ? '✅ Enabled' : '❌ Disabled';

            $message = "<b>Your Account Status</b>\n\n";
            $message .= "This Telegram account is linked to the following MHR System user:\n\n";
            $message .= "<b>Name:</b> " . e($user->full_name) . "\n";
            $message .= "<b>Email:</b> " . e($user->email) . "\n";
            $message .= "<b>Notification Status:</b> " . $status . "\n\n";
            $message .= "To change your notification preference, use /enable or /disable.";

            $this->telegramService->sendMessage($chatId, $message);
        } else {
            $message = "Your Telegram account is not linked to any MHR Reporting Compliance System account.\n\n";
            $message .= "Please use the <code>/link [your-email]</code> command to link your account.";

            $this->telegramService->sendMessage($chatId, $message);
        }
    }

    /**
     * Handle the /activity command.
     *
     * @param string $chatId
     * @param string|null $username
     * @return void
     */
    protected function handleActivityCommand($chatId, $username = null)
    {
        $user = $this->findUserByTelegram($chatId, $username);

        if (!$user) {
            $this->telegramService->sendMessage($chatId, "🚫 This Telegram account is not linked. Use /link to get started.");
            return;
        }

        // Fetch recent activities for the current user
        $activities = $user->activities()->latest()->take(10)->get();

        if ($activities->isEmpty()) {
            $this->telegramService->sendMessage($chatId, "You have no recent activity.");
            return;
        }

        $message = "<b>🔎 Your Recent Activities (Last 10)</b>\n\n";
        $message .= "<pre>";

        foreach ($activities as $key => $activity) {
            $activityType = ucwords(str_replace('_', ' ', $activity->activity_type));
            $activityTime = $activity->created_at->format('Y-m-d H:i:s T');

            $message .= "Event:    " . e($activityType) . "\n";
            $message .= "Desc:     " . e($activity->activity_description) . "\n";
            $message .= "Time:     " . $activityTime . "\n";
            $message .= "IP:       " . e($activity->ip_address) . "\n";

            if ($activity->device !== 'Unknown' && $activity->device !== null) {
                $deviceInfo = e($activity->device) . " (" . e($activity->platform) . ", " . e($activity->browser) . ")";
                $message .= "Device:   " . $deviceInfo . "\n";
            }
            if ($activity->location) {
                $message .= "Location: " . e($activity->location) . "\n";
            }

            // Add a separator between entries, but not for the last one
            if ($key < $activities->count() - 1) {
                $message .= "---------------------------------\n";
            }
        }

        $message .= "</pre>";

        $this->telegramService->sendMessage($chatId, $message, ['parse_mode' => 'HTML']);
    }

    /**
     * Handle the /pendings command.
     */
    protected function handlePendingsCommand($chatId, $username = null)
    {
        $user = $this->findUserByTelegram($chatId, $username);

        if (!$user) {
            $this->telegramService->sendMessage($chatId, "🚫 This Telegram account is not linked. Use /link to get started.");
            return;
        }

        // Fetch instructions assigned to the user that are unread OR not replied to by the user
        $instructions = Instruction::with(['sender', 'recipients'])
            ->whereHas('recipients', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where(function ($q) use ($user) {
                $q->whereHas('recipients', function ($qr) use ($user) {
                    $qr->where('user_id', $user->id)->where('is_read', false);
                })
                ->orWhereDoesntHave('replies', function ($qr) use ($user) {
                    $qr->where('user_id', $user->id);
                });
            })
            ->latest('instructions.created_at')
            ->take(10)
            ->get();

        if ($instructions->isEmpty()) {
            $this->telegramService->sendMessage($chatId, "✅ You have no pending instructions. Great job!\n\nTip: You will receive real-time notifications for new instructions and replies.");
            return;
        }

        $lines = [];
        $lines[] = '<b>📋 Your Pending Instructions</b>';
        $lines[] = '';
        foreach ($instructions as $idx => $ins) {
            $number = $idx + 1;
            $deadline = $ins->target_deadline ? $ins->target_deadline->format('M d, Y g:i A') : '—';
            $isUnread = (bool) optional($ins->recipients->firstWhere('id', $user->id))->pivot?->is_read === false;
            $hasReplied = $ins->replies()->where('user_id', $user->id)->exists();

            $lines[] = sprintf(
                "%d) <b>#%d</b> %s\n   <b>From:</b> %s\n   <b>Deadline:</b> %s\n   <b>Status:</b> %s%s",
                $number,
                $ins->id,
                e($ins->title),
                e($ins->sender->full_name),
                e($deadline),
                $isUnread ? '🔔 Not read' : '✅ Read',
                $hasReplied ? ' · 💬 Replied' : ' · ⏳ No reply'
            );
        }
        $lines[] = '';
        $lines[] = 'Reply directly here using:';
        $lines[] = '<code>/reply [instruction_id] [your message]</code>';

        $this->telegramService->sendMessage($chatId, implode("\n", $lines));
    }

    /**
     * Handle the /reply command.
     * Format: /reply <instruction_id> <message>
     */
    protected function handleReplyCommand($chatId, $username = null, $params = [], $rawMessage = [])
    {
        $user = $this->findUserByTelegram($chatId, $username);

        if (!$user) {
            $this->telegramService->sendMessage($chatId, "🚫 This Telegram account is not linked. Use /link to get started.");
            return;
        }

        // System admin cannot reply
        if ($user->roles === UserRole::SYSTEM_ADMIN) {
            $this->telegramService->sendMessage($chatId, "🚫 System Administrators cannot reply to instructions.");
            return;
        }

        if (count($params) < 2) {
            $this->telegramService->sendMessage($chatId, "Usage: <code>/reply [instruction_id] [your message]</code>");
            return;
        }

        $instructionId = (int) array_shift($params);
        $content = trim(implode(' ', $params));

        if ($instructionId <= 0 || $content === '') {
            $this->telegramService->sendMessage($chatId, "Usage: <code>/reply [instruction_id] [your message]</code>");
            return;
        }

        $instruction = Instruction::with(['recipients', 'sender'])->find($instructionId);
        if (!$instruction) {
            $this->telegramService->sendMessage($chatId, "🚫 Instruction not found.");
            return;
        }

        // Authorization
        if (!$instruction->canBeAccessedBy($user)) {
            $this->telegramService->sendMessage($chatId, "🚫 You do not have permission to reply to this instruction.");
            return;
        }

        // Validate content length (basic)
        $validator = Validator::make(['content' => $content], [
            'content' => 'required|string|max:5000',
        ]);
        if ($validator->fails()) {
            $this->telegramService->sendMessage($chatId, '🚫 ' . e($validator->errors()->first()));
            return;
        }

        DB::beginTransaction();
        try {
            $reply = InstructionReply::create([
                'instruction_id' => $instruction->id,
                'user_id' => $user->id,
                'content' => $content,
            ]);

            InstructionActivity::create([
                'instruction_id' => $instruction->id,
                'user_id' => $user->id,
                'action' => 'replied',
                'content' => $content,
            ]);

            // Notify sender if different
            if ($user->id !== $instruction->sender_id) {
                $instruction->sender->notify(new InstructionRepliedNotification($instruction, $user, $reply));
            }

            // Notify other recipients except the replier
            $recipients = $instruction->recipients()->where('user_id', '!=', $user->id)->get();
            foreach ($recipients as $recipient) {
                $recipient->notify(new InstructionRepliedNotification($instruction, $user, $reply));
            }

            // Log system activity
            UserActivityService::log(
                'instruction_replied',
                'Replied to instruction: ' . $instruction->title,
                ['instruction_id' => $instruction->id],
                $user
            );

            // Broadcast for real-time updates
            event(new InstructionRepliedEvent($instruction, $reply, $user));

            DB::commit();

            $this->telegramService->sendMessage($chatId, "✅ Reply posted to instruction #{$instruction->id}.\n\nPreview:\n" . e(Str::limit($content, 200)));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Telegram reply failed', [
                'instruction_id' => $instructionId,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            $this->telegramService->sendMessage($chatId, '🚫 Failed to post reply. Please try again later.');
        }
    }

    /**
     * Find user by Telegram chat ID or username.
     *
     * @param string|null $chatId
     * @param string|null $username
     * @return \App\Models\User|null
     */
    protected function findUserByTelegram($chatId = null, $username = null)
    {
        $query = User::query();

        if ($chatId) {
            $query->where('telegram_chat_id', $chatId);
        } elseif ($username) {
            $query->where('telegram_username', $username);
        } else {
            return null;
        }

        return $query->first();
    }

    /**
     * Set the webhook URL for the Telegram bot.
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function setWebhook(Request $request)
    {
        $customUrl = $request->input('url');

        try {
            $response = $this->telegramService->setWebhook($customUrl);

            if ($response) {
                return response()->json([
                    'status' => 'success',
                    'response' => $response
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to set webhook'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exception: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete the webhook URL for the Telegram bot.
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteWebhook()
    {
        try {
            $response = $this->telegramService->deleteWebhook();

            if ($response) {
                return response()->json([
                    'status' => 'success',
                    'response' => $response
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete webhook'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exception: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get information about the current webhook.
     *
     * @return \Illuminate\Http\Response
     */
    public function getWebhookInfo()
    {
        try {
            $response = $this->telegramService->getWebhookInfo();

            if ($response) {
                return response()->json([
                    'status' => 'success',
                    'response' => $response
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get webhook info'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exception: ' . $e->getMessage()
            ], 500);
        }
    }
}
