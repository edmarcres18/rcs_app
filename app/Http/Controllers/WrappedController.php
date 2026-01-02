<?php

namespace App\Http\Controllers;

use App\Models\UserActivity;
use App\Models\User;
use App\Services\UserActivityWrappedService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;

class WrappedController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except('share');
    }

    /**
     * Display the RCS Wrapped summary for the authenticated user.
     */
    public function index(Request $request, UserActivityWrappedService $service, ?int $year = null)
    {
        if (! Config::get('app.wrapped_enabled', true)) {
            abort(404);
        }

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

        $displayName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->email;
        $shareSlug = $this->buildShareSlug($user);
        $summary = $service->generateWrappedSummary($user->id, $selectedYear);

        if (!View::exists('wrapped.index')) {
            abort(500, 'wrapped.index view missing');
        }

        return view('wrapped.index', [
            'summary' => $summary,
            'selectedYear' => $selectedYear,
            'availableYears' => $availableYears,
            'user' => $user,
            'displayName' => $displayName,
            'shareSlug' => $shareSlug,
        ]);
    }

    /**
     * Publicly shareable view of a user's wrapped card (card-only).
     */
    public function share(Request $request, UserActivityWrappedService $service, string $userSlugOrId, ?int $year = null)
    {
        if (! Config::get('app.wrapped_enabled', true)) {
            abort(404);
        }

        $user = $this->findUserBySlugOrId($userSlugOrId);

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

        $displayName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->email;
        $summary = $service->generateWrappedSummary($user->id, $selectedYear);

        if (!View::exists('wrapped.share')) {
            abort(500, 'wrapped.share view missing');
        }

        return view('wrapped.share', [
            'summary' => $summary,
            'selectedYear' => $selectedYear,
            'availableYears' => $availableYears,
            'user' => $user,
            'displayName' => $displayName,
        ]);
    }

    /**
     * Resolve user by slug (first-last) or numeric id (backwards compatible).
     */
    protected function findUserBySlugOrId(string $slugOrId): User
    {
        $baseQuery = User::select('id', 'first_name', 'last_name', 'email');

        if (ctype_digit($slugOrId)) {
            return $baseQuery->findOrFail((int) $slugOrId);
        }

        $slug = Str::lower($slugOrId);

        $user = (clone $baseQuery)
            ->whereRaw("LOWER(REPLACE(CONCAT(COALESCE(first_name,''),' ',COALESCE(last_name,'')),' ','-')) = ?", [$slug])
            ->first();

        if (! $user) {
            $user = (clone $baseQuery)
                ->whereRaw("LOWER(REPLACE(SUBSTRING_INDEX(email,'@',1),' ','-')) = ?", [$slug])
                ->first();
        }

        if (! $user) {
            abort(404);
        }

        return $user;
    }

    /**
     * Build share slug from user name or email local part.
     */
    protected function buildShareSlug(User $user): string
    {
        $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
        if ($name !== '') {
            return Str::slug($name);
        }

        $emailLocal = Str::before($user->email ?? '', '@');
        return Str::slug($emailLocal ?: (string) $user->id);
    }
}
