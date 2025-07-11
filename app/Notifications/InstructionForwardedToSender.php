<?php

namespace App\Notifications;

use App\Models\Instruction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InstructionForwardedToSender extends Notification implements ShouldBroadcast
{
    use Queueable;

    protected $instruction;
    protected $forwarder;
    protected $recipients;

    /**
     * Create a new notification instance.
     *
     * @param \App\Models\Instruction $instruction
     * @param \App\Models\User $forwarder
     * @param \Illuminate\Database\Eloquent\Collection $recipients
     */
    public function __construct(Instruction $instruction, User $forwarder, Collection $recipients)
    {
        $this->instruction = $instruction;
        $this->forwarder = $forwarder;
        $this->recipients = $recipients;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  object  $notifiable
     * @return array
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  object  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        $recipientNames = $this->recipients->pluck('full_name')->implode(', ');

        return (new MailMessage)
            ->subject("Your Instruction '{$this->instruction->title}' was forwarded")
            ->greeting('Hello ' . $notifiable->first_name . ',')
            ->line("Your instruction, '{$this->instruction->title}', was forwarded by {$this->forwarder->full_name}.")
            ->line("It was forwarded to: {$recipientNames}.")
            ->action('View Instruction', route('instructions.show', $this->instruction));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  object  $notifiable
     * @return array
     */
    public function toArray(object $notifiable): array
    {
        $recipientNames = $this->recipients->pluck('full_name')->implode(', ');
        $body = "{$this->forwarder->full_name} forwarded your instruction to {$recipientNames}.";

        return [
            'instruction_id' => $this->instruction->id,
            'title' => "Instruction Forwarded",
            'body' => $body,
            'url' => route('instructions.show', $this->instruction->id),
            'forwarder_id' => $this->forwarder->id,
            'forwarder_name' => $this->forwarder->full_name,
            'type' => 'instruction_forwarded_to_sender'
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     *
     * @param  object  $notifiable
     * @return \Illuminate\Notifications\Messages\BroadcastMessage
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'data' => $this->toArray($notifiable)
        ]);
    }
}
