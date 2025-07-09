<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstructionReply extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'instruction_id',
        'user_id',
        'content',
        'attachment',
    ];

    /**
     * Get the instruction this reply belongs to.
     */
    public function instruction(): BelongsTo
    {
        return $this->belongsTo(Instruction::class);
    }

    /**
     * Get the user who made this reply.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attachment URL if it exists.
     */
    public function getAttachmentUrlAttribute()
    {
        return $this->attachment ? asset('reply/attachments/' . $this->attachment) : null;
    }
}
