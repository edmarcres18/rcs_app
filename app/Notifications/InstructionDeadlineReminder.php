<?php

namespace App\Notifications;

use App\Models\Instruction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class InstructionDeadlineReminder extends Notification implements ShouldBroadcast
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
        return ['mail', 'database', 'broadcast', 'telegram'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $deadline = \Carbon\Carbon::parse($this->instruction->target_deadline)->format('F d, Y');

        return (new MailMessage)
                    ->subject('Reminder: Instruction Deadline Approaching')
                    ->greeting('Hello ' . $notifiable->first_name . ',')
                    ->line('This is a reminder that the following instruction is due soon.')
                    ->line('Title: ' . $this->instruction->title)
                    ->line('Deadline: ' . $deadline)
                    ->line('You have not yet replied to this instruction.')
                    ->action('View Instruction', route('instructions.show', $this->instruction))
                    ->line('Please review and reply to this instruction as soon as possible.');
    }

    /**
     * Get the Telegram representation of the notification.
     *
     * @param  object  $notifiable
     * @return string
     */
    public function toTelegram(object $notifiable): string
    {
        $deadline = \Carbon\Carbon::parse($this->instruction->target_deadline)->format('F d, Y');
        $daysRemaining = \Carbon\Carbon::now()->diffInDays($this->instruction->target_deadline, false);
        $url = route('instructions.show', $this->instruction->id);

        return "<b>🔔 Deadline Reminder</b>\n\n" .
               "The instruction '<b>" . e($this->instruction->title) . "</b>' is due in <b>{$daysRemaining} days</b>.\n" .
               "<b>Deadline:</b> {$deadline}\n\n" .
               "Please review and reply to this instruction as soon as possible.\n" .
               "<a href=\"" . $url . "\">View Instruction</a>";
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
        $daysRemaining = \Carbon\Carbon::now()->diffInDays($this->instruction->target_deadline, false);
        $deadline = \Carbon\Carbon::parse($this->instruction->target_deadline)->format('F d, Y');

        return [
            'instruction_id' => $this->instruction->id,
            'title' => 'Deadline Reminder: ' . $this->instruction->title,
            'body' => "The deadline for this instruction is in {$daysRemaining} days ({$deadline}).",
            'url' => route('instructions.show', $this->instruction->id),
            'deadline' => $this->instruction->target_deadline,
            'type' => 'instruction_deadline_reminder'
        ];
    }
}
