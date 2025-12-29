<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserActivity;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class UserActivityWrappedService
{
    /**
    * Build a yearly "Wrapped" summary for a user.
    *
    * @return array{
    *     year:int,
    *     total:int,
    *     top_activity_type?:array{value:?string,count:int},
    *     top_activity_description?:array{value:?string,count:int},
    *     peak_day?:array{date:?string,count:int},
    *     peak_month?:array{month:?string,count:int},
    *     top_device?:array{value:?string,count:int},
    *     top_platform?:array{value:?string,count:int},
    *     top_browser?:array{value:?string,count:int},
    *     notable_detail?:?string,
    *     summary:string,
    *     suggested_visuals:array<int,array<string,string>>
    * }
    */
    public static function generate(User $user, ?int $year = null): array
    {
        $year = $year ?? now()->year;

        $activities = UserActivity::where('user_id', $user->id)
            ->whereYear('created_at', $year)
            ->get();

        if ($activities->isEmpty()) {
            $friendlyName = self::friendlyName($user);
            return [
                'year' => $year,
                'total' => 0,
                'summary' => "Hey {$friendlyName}! We couldn't find any activity for {$year} yet. Jump back in to build your story for next year. 🚀",
                'suggested_visuals' => [],
            ];
        }

        $total = $activities->count();

        [$topActivityType, $topActivityTypeCount] = self::topValueWithCount($activities, 'activity_type');
        [$topDescription, $topDescriptionCount] = self::topValueWithCount($activities, 'activity_description');
        [$topDevice, $topDeviceCount] = self::topValueWithCount($activities, 'device');
        [$topPlatform, $topPlatformCount] = self::topValueWithCount($activities, 'platform');
        [$topBrowser, $topBrowserCount] = self::topValueWithCount($activities, 'browser');

        [$peakDayDate, $peakDayCount] = self::peakDate($activities, 'Y-m-d');
        [$peakMonthValue, $peakMonthCount] = self::peakDate($activities, 'Y-m');

        $notableDetail = self::notableDetail($activities);

        $summary = self::buildSummary(
            $user,
            $year,
            $total,
            $topActivityType,
            $topActivityTypeCount,
            $topDescription,
            $topDescriptionCount,
            $peakDayDate,
            $peakDayCount,
            $topDevice,
            $topPlatform,
            $topBrowser,
            $notableDetail
        );

        return [
            'year' => $year,
            'total' => $total,
            'top_activity_type' => [
                'value' => $topActivityType,
                'count' => $topActivityTypeCount,
            ],
            'top_activity_description' => [
                'value' => $topDescription,
                'count' => $topDescriptionCount,
            ],
            'peak_day' => [
                'date' => $peakDayDate,
                'count' => $peakDayCount,
            ],
            'peak_month' => [
                'month' => $peakMonthValue,
                'count' => $peakMonthCount,
            ],
            'top_device' => [
                'value' => $topDevice,
                'count' => $topDeviceCount,
            ],
            'top_platform' => [
                'value' => $topPlatform,
                'count' => $topPlatformCount,
            ],
            'top_browser' => [
                'value' => $topBrowser,
                'count' => $topBrowserCount,
            ],
            'notable_detail' => $notableDetail,
            'summary' => $summary,
            'suggested_visuals' => self::suggestedVisuals(),
        ];
    }

    /**
    * Helper to fetch the top value and its count for a field.
    *
    * @return array{0:?string,1:int}
    */
    protected static function topValueWithCount(Collection $activities, string $field): array
    {
        $grouped = $activities
            ->groupBy($field)
            ->map
            ->count()
            ->sortDesc();

        $topValue = $grouped->keys()->first();
        $topCount = $grouped->first() ?? 0;

        return [$topValue, $topCount];
    }

    /**
    * Helper to get peak date/month and count.
    *
    * @param string $format e.g. Y-m-d or Y-m
    * @return array{0:?string,1:int}
    */
    protected static function peakDate(Collection $activities, string $format): array
    {
        $grouped = $activities
            ->groupBy(fn ($activity) => $activity->created_at?->format($format))
            ->map
            ->count()
            ->sortDesc();

        $topValue = $grouped->keys()->first();
        $count = $grouped->first() ?? 0;

        return [$topValue, $count];
    }

    /**
    * Extract a notable detail from the JSON "details" column without exposing sensitive data.
    */
    protected static function notableDetail(Collection $activities): ?string
    {
        $details = $activities->pluck('details')
            ->filter()
            ->flatMap(function ($detail) {
                if (!is_array($detail)) {
                    return [];
                }

                // Keep only scalar values to avoid exposing nested sensitive info.
                return collect($detail)
                    ->filter(fn ($value) => is_scalar($value) && !is_bool($value))
                    ->map(fn ($value, $key) => "{$key}: {$value}");
            })
            ->values();

        return $details->first();
    }

    /**
    * Construct the friendly summary sentence.
    */
    protected static function buildSummary(
        User $user,
        int $year,
        int $total,
        ?string $topActivityType,
        int $topActivityTypeCount,
        ?string $topDescription,
        int $topDescriptionCount,
        ?string $peakDayDate,
        int $peakDayCount,
        ?string $topDevice,
        ?string $topPlatform,
        ?string $topBrowser,
        ?string $notableDetail
    ): string {
        $name = self::friendlyName($user);

        $topTypeText = $topActivityType
            ? "mostly doing {$topActivityType} ({$topActivityTypeCount} times)"
            : 'across a mix of activities';

        $topDescriptionText = $topDescription
            ? "Your top activity was \"{$topDescription}\" ({$topDescriptionCount} times)."
            : '';

        $peakDayText = $peakDayDate
            ? "Peak day: " . Carbon::parse($peakDayDate)->format('F j') . " ({$peakDayCount} activities)."
            : '';

        $devicePlatformText = ($topDevice || $topPlatform || $topBrowser)
            ? "Mostly from " . collect([$topDevice, $topPlatform, $topBrowser])
                ->filter()
                ->implode(' / ') . "."
            : '';

        $notableText = $notableDetail ? "Notable detail: {$notableDetail}." : '';

        return trim(
            "Hey {$name}! 🎉 In {$year} you logged {$total} activities, {$topTypeText}. "
            . "{$topDescriptionText} {$peakDayText} {$devicePlatformText} {$notableText}"
        );
    }

    /**
    * Short friendly name fallback.
    */
    protected static function friendlyName(User $user): string
    {
        return $user->first_name
            ?? $user->name
            ?? $user->email
            ?? 'there';
    }

    /**
    * Suggested visuals to pair with the data.
    *
    * @return array<int,array<string,string>>
    */
    protected static function suggestedVisuals(): array
    {
        return [
            [
                'type' => 'bar',
                'title' => 'Activities by Month',
                'description' => 'Visualize activity volume across the year.',
            ],
            [
                'type' => 'pie',
                'title' => 'Activity Types',
                'description' => 'Share which types you did most.',
            ],
            [
                'type' => 'heatmap',
                'title' => 'Activity by Day',
                'description' => 'See peak days at a glance.',
            ],
        ];
    }
}
