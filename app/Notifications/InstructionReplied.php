<?php

namespace App\Notifications;

use App\Models\Instruction;
use App\Models\InstructionReply;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InstructionReplied extends Notification
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
        return ['mail', 'database'];
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
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'instruction_id' => $this->instruction->id,
            'title' => $this->instruction->title,
            'reply_id' => $this->reply->id,
            'replier_id' => $this->replier->id,
            'replier_name' => $this->replier->full_name,
            'reply_preview' => substr($this->reply->content, 0, 100),
            'type' => 'instruction_replied'
        ];
    }
}
