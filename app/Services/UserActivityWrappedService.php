<?php

namespace App\Services;

use App\Models\UserActivity;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class UserActivityWrappedService
{
    /**
    * Generate a yearly wrapped summary for a user.
    *
    * @param  int  $userId
    * @param  int  $year
    * @return array<string, mixed>
    */
    public function generateWrappedSummary(int $userId, int $year): array
    {
        $activities = UserActivity::query()
            ->where('user_id', $userId)
            ->whereYear('created_at', $year)
            ->get([
                'activity_type',
                'activity_description',
                'details',
                'device',
                'browser',
                'platform',
                'created_at',
            ]);

        if ($activities->isEmpty()) {
            return $this->emptySummary($year);
        }

        $total = $activities->count();

        $activityTypeCounts = $activities
            ->groupBy('activity_type')
            ->map->count()
            ->sortDesc();

        $topDescriptions = $activities
            ->groupBy('activity_description')
            ->map->count()
            ->sortDesc();

        $peakDayGroup = $activities
            ->groupBy(fn ($activity) => $activity->created_at->toDateString())
            ->map->count()
            ->sortDesc();

        $peakMonthGroup = $activities
            ->groupBy(fn ($activity) => $activity->created_at->format('Y-m'))
            ->map->count()
            ->sortDesc();

        $deviceCounts = $this->countByField($activities, 'device', $total);
        $platformCounts = $this->countByField($activities, 'platform', $total);
        $browserCounts = $this->countByField($activities, 'browser', $total);

        $monthlyData = $this->buildMonthlyData($activities, $year);

        $milestones = $this->extractMilestones($activities);

        return [
            'year' => $year,
            'total_activities' => $total,
            'top_activity_type' => $this->formatTopItem($activityTypeCounts),
            'activity_types' => $this->formatCounts($activityTypeCounts, $total),
            'top_activity_descriptions' => $this->formatCounts($topDescriptions, $total, 5),
            'peak_day' => $this->formatPeak($peakDayGroup),
            'peak_month' => $this->formatPeakMonth($peakMonthGroup),
            'devices' => $deviceCounts,
            'platforms' => $platformCounts,
            'browsers' => $browserCounts,
            'monthly_activity' => $monthlyData,
            'milestones' => $milestones,
        ];
    }

    /**
     * Default summary for empty data sets.
     */
    protected function emptySummary(int $year): array
    {
        return [
            'year' => $year,
            'total_activities' => 0,
            'top_activity_type' => null,
            'activity_types' => [],
            'top_activity_descriptions' => [],
            'peak_day' => null,
            'peak_month' => null,
            'devices' => [],
            'platforms' => [],
            'browsers' => [],
            'monthly_activity' => [
                'labels' => collect(range(1, 12))->map(fn ($m) => Carbon::create()->month($m)->format('M'))->toArray(),
                'data' => array_fill(0, 12, 0),
            ],
            'milestones' => [],
        ];
    }

    /**
     * Count occurrences of a field with percentages.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function countByField(Collection $activities, string $field, int $total): array
    {
        return $activities
            ->groupBy($field)
            ->map->count()
            ->sortDesc()
            ->map(function ($count, $label) use ($total) {
                $safeLabel = $label ?: 'Unknown';
                return [
                    'label' => $safeLabel,
                    'count' => $count,
                    'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Build monthly series data.
     */
    protected function buildMonthlyData(Collection $activities, int $year): array
    {
        $labels = [];
        $data = [];

        foreach (range(1, 12) as $month) {
            $labels[] = Carbon::create()->month($month)->format('M');
            $data[] = $activities->filter(
                fn ($activity) => $activity->created_at->year == $year && $activity->created_at->month == $month
            )->count();
        }

        return compact('labels', 'data');
    }

    /**
     * Extract milestone-like entries from the details JSON.
     *
     * @return array<int, string>
     */
    protected function extractMilestones(Collection $activities): array
    {
        return $activities
            ->pluck('details')
            ->filter(fn ($details) => is_array($details))
            ->flatMap(function ($details) {
                $candidates = collect($details)->only([
                    'milestone',
                    'milestones',
                    'achievement',
                    'achievements',
                    'highlight',
                    'highlights',
                    'note',
                    'notes',
                    'title',
                ])->flatten();

                return $candidates->filter()->map(fn ($item) => (string) $item);
            })
            ->filter()
            ->unique()
            ->values()
            ->take(10)
            ->toArray();
    }

    /**
     * Format top item for quick reference.
     */
    protected function formatTopItem(Collection $counts): ?array
    {
        if ($counts->isEmpty()) {
            return null;
        }

        $label = $counts->keys()->first();

        return [
            'label' => $label,
            'count' => $counts->first(),
        ];
    }

    /**
     * Format counts with optional limit.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function formatCounts(Collection $counts, int $total, int $limit = null): array
    {
        $collection = $counts;

        if ($limit) {
            $collection = $collection->take($limit);
        }

        return $collection
            ->map(function ($count, $label) use ($total) {
                return [
                    'label' => $label ?: 'Unknown',
                    'count' => $count,
                    'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Format peak date data.
     */
    protected function formatPeak(Collection $grouped): ?array
    {
        if ($grouped->isEmpty()) {
            return null;
        }

        $dateString = $grouped->keys()->first();

        return [
            'date' => Carbon::parse($dateString)->format('F j'),
            'count' => $grouped->first(),
        ];
    }

    /**
     * Format peak month data.
     */
    protected function formatPeakMonth(Collection $grouped): ?array
    {
        if ($grouped->isEmpty()) {
            return null;
        }

        $key = $grouped->keys()->first();
        $count = $grouped->first();

        [$year, $month] = explode('-', $key);

        return [
            'month' => (int) $month,
            'label' => Carbon::create()->month((int) $month)->format('F'),
            'count' => $count,
        ];
    }
}
