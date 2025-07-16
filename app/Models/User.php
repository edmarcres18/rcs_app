<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'telegram_chat_id',
        'telegram_username',
        'telegram_notifications_enabled',
        'avatar',
        'first_name',
        'middle_name',
        'last_name',
        'nickname',
        'roles',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'roles' => UserRole::class,
            'telegram_notifications_enabled' => 'boolean',
        ];
    }

    /**
     * Get the activities for the user.
     */
    public function activities()
    {
        return $this->hasMany(UserActivity::class);
    }

    /**
     * Get the instructions sent by this user.
     */
    public function sentInstructions(): HasMany
    {
        return $this->hasMany(Instruction::class, 'sender_id');
    }

    /**
     * Get the instructions assigned to this user.
     */
    public function receivedInstructions(): BelongsToMany
    {
        return $this->belongsToMany(Instruction::class, 'instruction_user')
            ->withPivot('is_read', 'forwarded_by_id')
            ->withTimestamps();
    }

    /**
     * Get the full name of the user.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name);
    }

    /**
     * Get the URL of the user's avatar.
     *
     * @return string
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('uploads/avatars/' . $this->avatar);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->full_name) . '&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Route notifications for the Telegram channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return string|null
     */
    public function routeNotificationForTelegram($notification)
    {
        // Only return the chat ID if telegram notifications are enabled
        return $this->telegram_notifications_enabled ? $this->telegram_chat_id : null;
    }

    /**
     * Check if the user has Telegram configured.
     *
     * @return bool
     */
    public function hasTelegram()
    {
        return !empty($this->telegram_chat_id) || !empty($this->telegram_username);
    }

    /**
     * Enable Telegram notifications for this user.
     *
     * @return bool
     */
    public function enableTelegramNotifications()
    {
        if ($this->hasTelegram()) {
            $this->telegram_notifications_enabled = true;
            $this->save();
            return true;
        }

        return false;
    }

    /**
     * Disable Telegram notifications for this user.
     *
     * @return void
     */
    public function disableTelegramNotifications()
    {
        $this->telegram_notifications_enabled = false;
        $this->save();
    }
}
