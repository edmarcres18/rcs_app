<?php

namespace App\Notifications;

use App\Models\Instruction;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class InstructionForwarded extends Notification implements ShouldBroadcast
{
    protected $instruction;
    protected $forwarder;
    protected $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(Instruction $instruction, User $forwarder, ?string $message = null)
    {
        $this->instruction = $instruction;
        $this->forwarder = $forwarder;
        $this->message = $message;
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
        $mail = (new MailMessage)
            ->subject('Instruction Forwarded to You: ' . $this->instruction->title)
            ->greeting('Hello ' . $notifiable->first_name . ',')
            ->line($this->forwarder->full_name . ' has forwarded an instruction to you.')
            ->line('Title: ' . $this->instruction->title)
            ->line('Original sender: ' . $this->instruction->sender->full_name);

        if ($this->message) {
            $mail->line('Message from ' . $this->forwarder->full_name . ': ' . $this->message);
        }

        return $mail
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
        $body = $this->forwarder->full_name . ' has forwarded an instruction to you: ' . $this->instruction->title;
        if ($this->message) {
            $body .= ' with the message: "' . $this->message . '"';
        }

        return [
            'instruction_id' => $this->instruction->id,
            'title' => 'Instruction Forwarded: ' . $this->instruction->title,
            'body' => $body,
            'url' => route('instructions.show', $this->instruction->id),
            'forwarder_id' => $this->forwarder->id,
            'forwarder_name' => $this->forwarder->full_name,
            'sender_id' => $this->instruction->sender_id,
            'sender_name' => $this->instruction->sender->full_name,
            'message' => $this->message,
            'type' => 'instruction_forwarded'
        ];
    }
}
