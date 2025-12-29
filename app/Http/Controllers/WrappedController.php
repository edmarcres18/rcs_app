<?php

namespace App\Http\Controllers;

use App\Models\UserActivity;
use App\Services\UserActivityWrappedService;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WrappedController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the RCS Wrapped summary for the authenticated user.
     */
    public function index(Request $request, UserActivityWrappedService $service, ?int $year = null)
    {
        $user = Auth::user();
        $slug = $this->buildNameSlug($user);

        $availableYears = UserActivity::query()
            ->where('user_id', $user->id)
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $selectedYear = $year ?? (int) ($request->input('year') ?? now()->year);

        if ($availableYears->isNotEmpty() && !$availableYears->contains($selectedYear)) {
            $selectedYear = $availableYears->first();
        }

        $summary = $service->generateWrappedSummary($user->id, $selectedYear);

        return view('wrapped.index', [
            'summary' => $summary,
            'selectedYear' => $selectedYear,
            'availableYears' => $availableYears,
            'user' => $user,
            'shareSlug' => $slug,
        ]);
    }

    /**
     * Publicly shareable view of a user's wrapped card (card-only).
     */
    public function share(Request $request, UserActivityWrappedService $service, string $slug, ?int $year = null)
    {
        $user = $this->findUserBySlug($slug);

        $availableYears = UserActivity::query()
            ->where('user_id', $user->id)
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $selectedYear = $year ?? (int) ($request->input('year') ?? now()->year);

        if ($availableYears->isNotEmpty() && !$availableYears->contains($selectedYear)) {
            $selectedYear = $availableYears->first();
        }

        $summary = $service->generateWrappedSummary($user->id, $selectedYear);

        return view('wrapped.share', [
            'summary' => $summary,
            'selectedYear' => $selectedYear,
            'availableYears' => $availableYears,
            'user' => $user,
            'shareSlug' => $slug,
        ]);
    }

    /**
     * Build a slug from user's name parts.
     */
    protected function buildNameSlug(User $user): string
    {
        $fullName = trim(implode(' ', array_filter([
            $user->first_name ?? '',
            $user->middle_name ?? '',
            $user->last_name ?? '',
        ])));

        if ($fullName === '') {
            return Str::slug($user->email ?? 'user');
        }

        return Str::slug($fullName);
    }

    /**
     * Find user by name slug (first-middle-last).
     */
    protected function findUserBySlug(string $slug): User
    {
        $target = Str::lower($slug);

        return User::whereRaw("LOWER(REPLACE(TRIM(CONCAT_WS(' ', first_name, middle_name, last_name)), ' ', '-')) = ?", [$target])
            ->firstOrFail();
    }
}
