<?php

namespace App\Services;

use App\Models\UserActivity;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UserActivityWrappedService
{
    /**
    * Generate a yearly wrapped summary for a user.
    *
    * Cached for 2 hours to reduce load; cache key is user/year specific.
    */
    public function generateWrappedSummary(int $userId, int $year): array
    {
        $cacheKey = "wrapped:summary:{$userId}:{$year}";

        return Cache::remember($cacheKey, now()->addHours(2), function () use ($userId, $year) {
            $base = UserActivity::query()
                ->where('user_id', $userId)
                ->whereYear('created_at', $year);

            $total = (clone $base)->count();
            if ($total === 0) {
                return $this->emptySummary($year);
            }

            $activityTypeCounts = $this->countCollection(
                (clone $base)
                    ->select('activity_type', DB::raw('count(*) as aggregate_count'))
                    ->groupBy('activity_type')
                    ->orderByDesc('aggregate_count')
                    ->get(),
                'activity_type'
            );

            $topDescriptions = $this->countCollection(
                (clone $base)
                    ->select('activity_description', DB::raw('count(*) as aggregate_count'))
                    ->groupBy('activity_description')
                    ->orderByDesc('aggregate_count')
                    ->get(),
                'activity_description'
            );

            $peakDayGroup = $this->countCollection(
                (clone $base)
                    ->selectRaw('DATE(created_at) as bucket, count(*) as aggregate_count')
                    ->groupBy('bucket')
                    ->orderByDesc('aggregate_count')
                    ->get(),
                'bucket'
            );

            $peakMonthGroup = $this->countCollection(
                (clone $base)
                    ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as bucket, count(*) as aggregate_count")
                    ->groupBy('bucket')
                    ->orderByDesc('aggregate_count')
                    ->get(),
                'bucket'
            );

            $deviceCounts = $this->countCollection(
                (clone $base)
                    ->select('device', DB::raw('count(*) as aggregate_count'))
                    ->groupBy('device')
                    ->orderByDesc('aggregate_count')
                    ->get(),
                'device'
            );

            $platformCounts = $this->countCollection(
                (clone $base)
                    ->select('platform', DB::raw('count(*) as aggregate_count'))
                    ->groupBy('platform')
                    ->orderByDesc('aggregate_count')
                    ->get(),
                'platform'
            );

            $browserCounts = $this->countCollection(
                (clone $base)
                    ->select('browser', DB::raw('count(*) as aggregate_count'))
                    ->groupBy('browser')
                    ->orderByDesc('aggregate_count')
                    ->get(),
                'browser'
            );

            $monthlyData = $this->buildMonthlyData($base, $year);

            $milestones = $this->extractMilestones($base);

            return [
                'year' => $year,
                'total_activities' => $total,
                'top_activity_type' => $this->formatTopItem($activityTypeCounts),
                'activity_types' => $this->formatCounts($activityTypeCounts, $total),
                'top_activity_descriptions' => $this->formatCounts($topDescriptions, $total, 5),
                'peak_day' => $this->formatPeak($peakDayGroup),
                'peak_month' => $this->formatPeakMonth($peakMonthGroup),
                'devices' => $this->formatCountTuples($deviceCounts, $total),
                'platforms' => $this->formatCountTuples($platformCounts, $total),
                'browsers' => $this->formatCountTuples($browserCounts, $total),
                'monthly_activity' => $monthlyData,
                'milestones' => $milestones,
            ];
        });
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
     * Build monthly series data using aggregated query.
     */
    protected function buildMonthlyData($baseQuery, int $year): array
    {
        $labels = [];
        $data = [];

        $monthly = (clone $baseQuery)
            ->selectRaw('MONTH(created_at) as month_num, count(*) as aggregate_count')
            ->groupBy('month_num')
            ->pluck('aggregate_count', 'month_num');

        foreach (range(1, 12) as $month) {
            $labels[] = Carbon::create()->month($month)->format('M');
            $data[] = (int) ($monthly[$month] ?? 0);
        }

        return compact('labels', 'data');
    }

    /**
     * Extract milestone-like entries from the details JSON.
     *
     * @return array<int, string>
     */
    protected function extractMilestones($baseQuery): array
    {
        $milestones = collect();

        (clone $baseQuery)
            ->select('details')
            ->whereNotNull('details')
            ->chunk(1000, function ($chunk) use ($milestones) {
                foreach ($chunk as $row) {
                    if (!is_array($row->details)) {
                        continue;
                    }
                    $candidates = collect($row->details)->only([
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

                    $milestones->push(...$candidates->filter()->map(fn ($item) => (string) $item));
                }
            });

        return $milestones
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
            'label' => $this->humanizeLabel($label),
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
                    'label' => $this->humanizeLabel($label ?: 'Unknown'),
                    'count' => $count,
                    'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Humanize labels (e.g., login -> Login, instructions_replied -> Instructions Replied).
     */
    protected function humanizeLabel(?string $value): string
    {
        $label = str_replace(['_', '-'], ' ', strtolower($value ?? ''));
        return ucwords(trim($label));
    }

    /**
     * Convert aggregated rows to keyed counts.
     */
    protected function countCollection(Collection $rows, string $labelKey): Collection
    {
        return $rows
            ->mapWithKeys(function ($row) use ($labelKey) {
                $label = $this->humanizeLabel($row->{$labelKey} ?? 'Unknown');
                return [$label => (int) $row->aggregate_count];
            })
            ->sortDesc();
    }

    /**
     * Convert keyed count collection to array of tuples with percentages.
     *
     * @return array<int, array{label:string,count:int,percentage:float}>
     */
    protected function formatCountTuples(Collection $counts, int $total): array
    {
        return $counts
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
