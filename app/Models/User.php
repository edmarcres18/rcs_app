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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\SystemNotifications;

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

    /**
     * Get the ratings submitted by this user.
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * Submit a rating for this user with comprehensive validation and error handling.
     *
     * @param array $data Rating data containing 'rating' and optional 'comment'
     * @param string|null $ipAddress User's IP address for tracking
     * @param string|null $userAgent User's browser agent for tracking
     * @return Rating The created rating instance
     * @throws ValidationException If validation fails
     * @throws \Exception If database operation fails
     */
    public function submitRating(array $data, ?string $ipAddress = null, ?string $userAgent = null): Rating
    {
        // Validate input data
        $this->validateRatingData($data);

        // Check for rate limiting (prevent spam)
        $this->checkRateLimit();

        DB::beginTransaction();

        try {
            // Prepare rating data
            $ratingData = [
                'user_id' => $this->id,
                'rating' => (int) $data['rating'],
                'comment' => isset($data['comment']) ? trim($data['comment']) : null,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'submitted_at' => now(),
            ];

            // Remove empty comment
            if (empty($ratingData['comment'])) {
                $ratingData['comment'] = null;
            }

            // Create the rating
            $rating = Rating::create($ratingData);

            // Log the successful rating submission
            Log::info('Rating submitted successfully', [
                'user_id' => $this->id,
                'rating_id' => $rating->id,
                'rating_value' => $rating->rating,
                'has_comment' => !empty($rating->comment),
                'ip_address' => $ipAddress,
            ]);

            DB::commit();

            return $rating;

        } catch (\Exception $e) {
            DB::rollBack();

            // Log the error
            Log::error('Failed to submit rating', [
                'user_id' => $this->id,
                'error' => $e->getMessage(),
                'data' => $data,
                'ip_address' => $ipAddress,
            ]);

            throw new \Exception('Failed to submit rating. Please try again later.');
        }
    }

    /**
     * Validate rating data before submission.
     *
     * @param array $data
     * @throws ValidationException
     */
    private function validateRatingData(array $data): void
    {
        // Check if rating is provided
        if (!isset($data['rating'])) {
            throw ValidationException::withMessages([
                'rating' => ['Rating is required.']
            ]);
        }

        // Validate rating value
        $rating = $data['rating'];
        if (!is_numeric($rating) || $rating < 1 || $rating > 5) {
            throw ValidationException::withMessages([
                'rating' => ['Rating must be a number between 1 and 5.']
            ]);
        }

        // Validate comment if provided
        if (isset($data['comment']) && !empty($data['comment'])) {
            $comment = trim($data['comment']);
            if (strlen($comment) > 1000) {
                throw ValidationException::withMessages([
                    'comment' => ['Comment cannot exceed 1000 characters.']
                ]);
            }

            // Basic content validation (no malicious content)
            if (preg_match('/<script|javascript:|data:/i', $comment)) {
                throw ValidationException::withMessages([
                    'comment' => ['Comment contains invalid content.']
                ]);
            }
        }
    }

    /**
     * Check rate limiting to prevent spam submissions.
     *
     * @throws ValidationException
     */
    private function checkRateLimit(): void
    {
        // Check if user has submitted a rating in the last 24 hours
        $recentRating = $this->ratings()
            ->where('submitted_at', '>=', now()->subDay())
            ->first();

        if ($recentRating) {
            throw ValidationException::withMessages([
                'rating' => ['You can only submit one rating per day. Please try again tomorrow.']
            ]);
        }

        // Check if user has submitted more than 5 ratings in the last week
        $weeklyRatingsCount = $this->ratings()
            ->where('submitted_at', '>=', now()->subWeek())
            ->count();

        if ($weeklyRatingsCount >= 5) {
            throw ValidationException::withMessages([
                'rating' => ['You have reached the weekly rating limit. Please try again next week.']
            ]);
        }
    }

    /**
     * Get the user's latest rating.
     *
     * @return Rating|null
     */
    public function getLatestRating(): ?Rating
    {
        return $this->ratings()->latest('submitted_at')->first();
    }

    /**
     * Get the user's average rating.
     *
     * @return float|null
     */
    public function getAverageRating(): ?float
    {
        $average = $this->ratings()->avg('rating');
        return $average ? round($average, 2) : null;
    }

    /**
     * Check if user can submit a rating (not rate limited).
     *
     * @return bool
     */
    public function canSubmitRating(): bool
    {
        try {
            $this->checkRateLimit();
            return true;
        } catch (ValidationException $e) {
            return false;
        }
    }

    /**
     * Get the time until user can submit next rating.
     *
     * @return \Carbon\Carbon|null
     */
    public function getNextRatingTime(): ?\Carbon\Carbon
    {
        $latestRating = $this->getLatestRating();

        if (!$latestRating) {
            return null;
        }

        $nextAllowedTime = $latestRating->submitted_at->addDay();

        return $nextAllowedTime->isFuture() ? $nextAllowedTime : null;
    }

    public function systemNotifications(): BelongsTo
    {
        return $this->belongsTo(SystemNotifications::class, 'created_by');
    }
}
