<?php

namespace App\Notifications;

use App\Models\Instruction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InstructionAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    protected $instruction;

    /**
     * Create a new notification instance.
     */
    public function __construct(Instruction $instruction)
    {
        $this->instruction = $instruction;
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
            ->subject('New Instruction Assigned: ' . $this->instruction->title)
            ->greeting('Hello ' . $notifiable->first_name . ',')
            ->line('You have been assigned a new instruction.')
            ->line('Title: ' . $this->instruction->title)
            ->line('From: ' . $this->instruction->sender->full_name)
            ->action('View Instruction', route('instructions.show', $this->instruction))
            ->line('Please review this instruction as soon as possible.');
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
            'sender_id' => $this->instruction->sender_id,
            'sender_name' => $this->instruction->sender->full_name,
            'type' => 'instruction_assigned'
        ];
    }
}
