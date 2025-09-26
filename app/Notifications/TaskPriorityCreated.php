<?php

namespace App\Notifications;

use App\Models\Instruction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\TelegramChannel;

class TaskPriorityCreated extends Notification
{
    use Queueable;

    public function __construct(
        public Instruction $instruction,
        public \Illuminate\Support\Collection $items, // collection of TaskPriority
        public $createdBy
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        // No ShouldQueue used -> send now (synchronous)
        return ['mail', 'database', TelegramChannel::class, 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $count = $this->items->count();
        $title = (string) ($this->instruction->title ?? 'Instruction');
        $creator = $this->createdBy->full_name ?? ($this->createdBy->name ?? 'Unknown User');

        $mail = (new MailMessage)
            ->subject("New Task Priorities (x{$count}) for: {$title}")
            ->greeting('Hello '.$notifiable->full_name)
            ->line("{$creator} created {$count} task priority item(s) for the instruction:")
            ->line('“'.$title.'”')
            ->action('View Task Priorities', route('task-priorities.index'))
            ->line('Summary:');

        foreach ($this->items->take(5) as $i) {
            $mail->line("• ".$i->priority_title." — ".($i->status)." — Deadline: ".optional($i->target_deadline)->format('Y-m-d'));
        }
        if ($count > 5) {
            $mail->line('…and more.');
        }

        return $mail;
    }

    /**
     * Database payload for in-app notifications.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task_priority_created',
            'instruction_id' => $this->instruction->id,
            'instruction_title' => $this->instruction->title,
            'created_by' => $this->createdBy->only(['id','full_name','name']),
            'count' => $this->items->count(),
            'items' => $this->items->map(function ($i) {
                return [
                    'id' => $i->id,
                    'priority_title' => $i->priority_title,
                    'status' => $i->status,
                    'target_deadline' => optional($i->target_deadline)->format('Y-m-d'),
                ];
            }),
            'url' => route('task-priorities.index'),
        ];
    }

    /**
     * Broadcasted payload for real-time updates.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    /**
     * Telegram content using custom TelegramChannel.
     */
    public function toTelegram(object $notifiable): string
    {
        $count = $this->items->count();
        $title = (string) ($this->instruction->title ?? 'Instruction');
        $creator = $this->createdBy->full_name ?? ($this->createdBy->name ?? 'User');
        $lines = [
            "🆕 New Task Priorities (x{$count})",
            "Instruction: {$title}",
            "Creator: {$creator}",
            "Items:",
        ];
        foreach ($this->items->take(5) as $i) {
            $lines[] = '• '.$i->priority_title.' — '.$i->status.' — '.optional($i->target_deadline)->format('Y-m-d');
        }
        if ($count > 5) {
            $lines[] = '…and more.';
        }
        $lines[] = url(route('task-priorities.index', absolute: false));
        return implode("\n", $lines);
    }
}
