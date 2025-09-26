<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Instruction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sender_id',
        'title',
        'body',
        'target_deadline',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'target_deadline' => 'datetime',
    ];

    /**
     * Get the sender of the instruction.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the users assigned to this instruction.
     */
    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'instruction_user')
            ->withPivot('is_read', 'forwarded_by_id')
            ->withTimestamps();
    }

    /**
     * Get activities for this instruction.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(InstructionActivity::class);
    }

    /**
     * Get replies for this instruction.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(InstructionReply::class);
    }

    /**
     * Get task priorities for this instruction.
     */
    public function taskPriorities(): HasMany
    {
        return $this->hasMany(TaskPriority::class);
    }

    /**
     * Check if the given user can access this instruction.
     */
    public function canBeAccessedBy(User $user): bool
    {
        // SYSTEM_ADMIN can monitor all instructions
        if ($user->roles->value === 'SYSTEM_ADMIN') {
            return true;
        }

        // ADMIN can monitor all instructions
        if ($user->roles->value === 'ADMIN') {
            return true;
        }

        // Sender can access their own instructions
        if ($this->sender_id === $user->id) {
            return true;
        }

        // Recipients can access instructions assigned to them
        return $this->recipients()->where('user_id', $user->id)->exists();
    }

    public function getRoleNameAttribute(): string
    {
        return $this->sender->roles;
    }
}
