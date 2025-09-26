<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskPriority extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'group_key',
        'instruction_id',
        'instruction_sender_id',
        'created_by_user_id',
        'priority_title',
        'priority_level',
        'start_date',
        'target_deadline',
        'week_range',
        'status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'target_deadline' => 'date',
            'week_range' => 'integer',
        ];
    }

    /**
     * Get the instruction that owns the task priority.
     */
    public function instruction(): BelongsTo
    {
        return $this->belongsTo(Instruction::class);
    }

    /**
     * Get the sender of the instruction.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instruction_sender_id');
    }

    /**
     * The receiver (creator) who created this task priority group.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Calculate and set the week range based on start date and target deadline.
     *
     * @param  string|Carbon  $startDate
     * @param  string|Carbon  $targetDeadline
     */
    public static function calculateWeekRange($startDate, $targetDeadline): int
    {
        $start = Carbon::parse($startDate);
        $deadline = Carbon::parse($targetDeadline);

        $daysDifference = $start->diffInDays($deadline);
        $weeks = (int) ceil($daysDifference / 7);

        // Clamp to range [1, 5]
        return max(1, min(5, $weeks));
    }

    /**
     * Get the priority level badge color.
     */
    public function getPriorityLevelBadgeColorAttribute(): string
    {
        return match ($this->priority_level) {
            'high' => 'danger',
            'normal' => 'warning',
            'low' => 'success',
            default => 'secondary',
        };
    }

    /**
     * Get the status badge color.
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'Not Started' => 'secondary',
            'Processing' => 'primary',
            'Accomplished' => 'success',
            default => 'secondary',
        };
    }

    /**
     * Check if the task priority is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->target_deadline->isPast() && $this->status !== 'Accomplished';
    }

    /**
     * Check if the task priority is due soon (within 3 days).
     */
    public function isDueSoon(): bool
    {
        return $this->target_deadline->isFuture() &&
               $this->target_deadline->diffInDays(now()) <= 3 &&
               $this->status !== 'Accomplished';
    }
}
