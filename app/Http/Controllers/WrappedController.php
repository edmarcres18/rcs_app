<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserActivity;
use App\Services\UserActivityWrappedService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
        $shareSlug = $this->buildNameSlug($user);

        return view('wrapped.index', [
            'summary' => $summary,
            'selectedYear' => $selectedYear,
            'availableYears' => $availableYears,
            'user' => $user,
            'shareSlug' => $shareSlug,
        ]);
    }

    /**
     * Publicly shareable view of a user's wrapped card (card-only).
     */
    public function share(Request $request, UserActivityWrappedService $service, string $slug, ?int $year = null)
    {
        $user = $this->findUserBySlugOrFail($slug);

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
        ]);
    }

    /**
     * Build a public-friendly slug from name parts.
     */
    protected function buildNameSlug(User $user): string
    {
        $parts = array_filter([
            $user->first_name ?? '',
            $user->middle_name ?? '',
            $user->last_name ?? '',
        ]);

        $base = Str::slug(implode(' ', $parts));
        if (!$base) {
            $base = 'user';
        }

        // Attach user id to avoid collisions and ensure deterministic lookups
        return "{$base}-u{$user->id}";
    }

    /**
     * Find a user by slug derived from name parts.
     */
    protected function findUserBySlugOrFail(string $slug): User
    {
        // Prefer deterministic lookup by id suffix when present
        if (preg_match('/-u(\d+)$/', $slug, $matches)) {
            $id = (int) $matches[1];
            $user = User::find($id);
            if ($user && $this->buildNameSlug($user) === $slug) {
                return $user;
            }
        }

        // Fallback: scan for matching slug (should rarely be used)
        $user = User::select('id', 'first_name', 'middle_name', 'last_name', 'email')
            ->get()
            ->first(function ($candidate) use ($slug) {
                return $this->buildNameSlug($candidate) === $slug;
            });

        if (!$user) {
            abort(404, 'User not found');
        }

        return $user;
    }
}
