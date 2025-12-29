<?php

namespace App\Http\Controllers;

use App\Models\UserActivity;
use App\Models\User;
use App\Services\UserActivityWrappedService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

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
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        $availableYears = UserActivity::query()
            ->where('user_id', $user->id)
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $selectedYear = $year ?? (int) ($request->input('year') ?? now()->year);
        if ($selectedYear < 2000 || $selectedYear > (int) now()->format('Y') + 1) {
            $selectedYear = (int) now()->year;
        }

        if ($availableYears->isNotEmpty() && !$availableYears->contains($selectedYear)) {
            $selectedYear = $availableYears->first();
        }

        try {
            $summary = $service->generateWrappedSummary($user->id, $selectedYear);
        } catch (\Throwable $e) {
            \Log::error('Wrapped summary generation failed', [
                'user_id' => $user->id,
                'year' => $selectedYear,
                'error' => $e->getMessage(),
            ]);
            abort(500, 'Unable to generate wrapped summary at this time.');
        }

        // secure, expirable signed URL
        $shareUrl = URL::temporarySignedRoute(
            'wrapped.share',
            now()->addDay(),
            [
                'token' => Str::random(24),
                'uid' => $user->id,
                'year' => $selectedYear,
            ]
        );

        return view('wrapped.index', [
            'summary' => $summary,
            'selectedYear' => $selectedYear,
            'availableYears' => $availableYears,
            'user' => $user,
            'shareUrl' => $shareUrl,
        ]);
    }

    /**
     * Publicly shareable view of a user's wrapped card (card-only).
     */
    public function share(Request $request, UserActivityWrappedService $service, string $token)
    {
        if (!$request->hasValidSignature()) {
            abort(403);
        }

        $userId = (int) $request->query('uid');
        $year = $request->query('year');

        $user = User::findOrFail($userId);

        $availableYears = UserActivity::query()
            ->where('user_id', $user->id)
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $selectedYear = $year ? (int) $year : (int) ($request->input('year') ?? now()->year);
        if ($selectedYear < 2000 || $selectedYear > (int) now()->format('Y') + 1) {
            $selectedYear = (int) now()->year;
        }

        if ($availableYears->isNotEmpty() && !$availableYears->contains($selectedYear)) {
            $selectedYear = $availableYears->first();
        }

        try {
            $summary = $service->generateWrappedSummary($user->id, $selectedYear);
        } catch (\Throwable $e) {
            \Log::error('Wrapped public summary generation failed', [
                'user_id' => $user->id,
                'year' => $selectedYear,
                'error' => $e->getMessage(),
            ]);
            abort(500, 'Unable to load wrapped summary.');
        }

        return view('wrapped.share', [
            'summary' => $summary,
            'selectedYear' => $selectedYear,
            'availableYears' => $availableYears,
            'user' => $user,
            'shareUrl' => $request->fullUrl(),
        ]);
    }
}
