<?php

namespace App\Notifications;

use App\Models\Instruction;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;

class InstructionAssigned extends Notification implements ShouldBroadcast, ShouldQueue
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
        return ['mail', 'database', 'broadcast'];
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
            'title' => 'New Instruction: ' . $this->instruction->title,
            'body' => 'You have been assigned a new instruction from ' . $this->instruction->sender->full_name,
            'url' => route('instructions.show', $this->instruction->id),
            'sender_id' => $this->instruction->sender_id,
            'sender_name' => $this->instruction->sender->full_name,
            'type' => 'instruction_assigned'
        ];
    }
}
