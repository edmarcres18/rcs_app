<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use App\Services\FileUploadService;

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
        'attachment_filename',
        'attachment_original_name',
        'attachment_path',
        'attachment_mime_type',
        'attachment_size',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
     * Check if this reply has an attachment.
     */
    public function hasAttachment(): bool
    {
        return !empty($this->attachment_path);
    }

    /**
     * Get the attachment URL.
     */
    public function getAttachmentUrlAttribute(): ?string
    {
        if (!$this->hasAttachment()) {
            return null;
        }

        return Storage::disk('public')->url($this->attachment_path);
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedFileSizeAttribute(): ?string
    {
        if (!$this->attachment_size) {
            return null;
        }

        return FileUploadService::formatFileSize($this->attachment_size);
    }

    /**
     * Check if attachment is an image.
     */
    public function isAttachmentImage(): bool
    {
        if (!$this->attachment_mime_type) {
            return false;
        }

        return FileUploadService::isImage($this->attachment_mime_type);
    }

    /**
     * Get file icon class for the attachment.
     */
    public function getAttachmentIconAttribute(): string
    {
        if (!$this->hasAttachment()) {
            return 'fas fa-file';
        }

        $extension = pathinfo($this->attachment_original_name, PATHINFO_EXTENSION);
        return FileUploadService::getFileIcon($extension, $this->attachment_mime_type);
    }

    /**
     * Delete the attachment file when the reply is deleted.
     */
    protected static function booted()
    {
        static::deleting(function ($reply) {
            if ($reply->hasAttachment()) {
                Storage::disk('public')->delete($reply->attachment_path);
            }
        });
    }
}
