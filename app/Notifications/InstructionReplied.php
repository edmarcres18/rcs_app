<?php

namespace App\Notifications;

use App\Models\Instruction;
use App\Models\InstructionReply;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class InstructionReplied extends Notification implements ShouldBroadcast
{
    protected $instruction;
    protected $replier;
    protected $reply;

    /**
     * Create a new notification instance.
     */
    public function __construct(Instruction $instruction, User $replier, InstructionReply $reply)
    {
        $this->instruction = $instruction;
        $this->replier = $replier;
        $this->reply = $reply;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast', 'telegram'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Reply to Instruction: ' . $this->instruction->title)
            ->greeting('Hello ' . $notifiable->first_name . ',')
            ->line($this->replier->full_name . ' has replied to your instruction.')
            ->line('Title: ' . $this->instruction->title)
            ->line('Reply: ' . substr($this->reply->content, 0, 100) . (strlen($this->reply->content) > 100 ? '...' : ''))
            ->action('View Instruction', route('instructions.show', $this->instruction))
            ->line('Please review this reply as soon as possible.');
    }

    /**
     * Get the Telegram representation of the notification.
     *
     * @param  object  $notifiable
     * @return string
     */
    public function toTelegram(object $notifiable): string
    {
        $url = route('instructions.show', $this->instruction->id);
        $replyPreview = substr($this->reply->content, 0, 100) . (strlen($this->reply->content) > 100 ? '...' : '');

        return "<b>💬 New Reply to Instruction</b>\n\n" .
               e($this->replier->full_name) . " has replied to your instruction '<b>" . e($this->instruction->title) . "</b>'.\n\n" .
               "<b>Reply:</b> \"" . e($replyPreview) . "\"\n\n" .
               "<a href=\"" . $url . "\">View Reply</a>";
    }

    /**
     * Get the broadcastable representation of the notification.
     *
     * @param  object  $notifiable
     * @return \Illuminate\Notifications\Messages\BroadcastMessage
     */
    public function toBroadcast(object $notifiable): \Illuminate\Notifications\Messages\BroadcastMessage
    {
        return new \Illuminate\Notifications\Messages\BroadcastMessage([
            'id' => $this->id,
            'data' => $this->toArray($notifiable)
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'instruction_id' => $this->instruction->id,
            'title' => 'New Reply: ' . $this->instruction->title,
            'body' => $this->replier->full_name . ' has replied to the instruction: ' . $this->instruction->title,
            'url' => route('instructions.show', $this->instruction->id),
            'reply_id' => $this->reply->id,
            'replier_id' => $this->replier->id,
            'replier_name' => $this->replier->full_name,
            'reply_preview' => substr($this->reply->content, 0, 100),
            'type' => 'instruction_replied'
        ];
    }
}
