<?php

namespace App\Http\Controllers;

use App\Models\UserActivity;
use App\Services\UserActivityWrappedService;
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
        ]);
    }

    /**
     * Publicly shareable view of a user's wrapped card (card-only).
     */
    public function share(Request $request, UserActivityWrappedService $service, int $userId, ?int $year = null)
    {
        $user = User::findOrFail($userId);

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
}
