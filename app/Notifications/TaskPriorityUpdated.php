<?php

namespace App\Notifications;

use App\Models\Instruction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\TelegramChannel;

class TaskPriorityUpdated extends Notification
{
    use Queueable;

    public function __construct(
        public Instruction $instruction,
        public \Illuminate\Support\Collection $items, // collection of TaskPriority (updated set)
        public $updatedBy
    ) {}

    public function via(object $notifiable): array
    {
        // Send synchronously when used with notifyNow()
        return ['mail', 'database', TelegramChannel::class, 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $count = $this->items->count();
        $title = (string) ($this->instruction->title ?? 'Instruction');
        $actor = $this->updatedBy->full_name ?? ($this->updatedBy->name ?? 'Unknown User');

        $mail = (new MailMessage)
            ->subject("Task Priorities Updated (x{$count}) – {$title}")
            ->greeting('Hello '.$notifiable->full_name)
            ->line("{$actor} updated {$count} task priority item(s) for the instruction:")
            ->line('“'.$title.'”')
            ->action('Review Updates', route('task-priorities.index'))
            ->line('Recent items:');

        foreach ($this->items->take(5) as $i) {
            $mail->line("• ".$i->priority_title." — ".($i->status)." — Deadline: ".optional($i->target_deadline)->format('Y-m-d'));
        }
        if ($count > 5) {
            $mail->line('…and more.');
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task_priority_updated',
            'instruction_id' => $this->instruction->id,
            'instruction_title' => $this->instruction->title,
            'updated_by' => $this->updatedBy->only(['id','full_name','name']),
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

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function toTelegram(object $notifiable): string
    {
        $count = $this->items->count();
        $title = (string) ($this->instruction->title ?? 'Instruction');
        $actor = $this->updatedBy->full_name ?? ($this->updatedBy->name ?? 'User');
        $lines = [
            "♻️ Task Priorities Updated (x{$count})",
            "Instruction: {$title}",
            "By: {$actor}",
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
