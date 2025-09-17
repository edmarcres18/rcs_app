<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class RatingController extends Controller
{
    /**
     * Store a newly created rating in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Get the authenticated user
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated.',
                ], 401);
            }

            // Check if user has already submitted a rating
            if ($user->ratings()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already submitted a rating. Only one rating is allowed per user.',
                ], 403);
            }
            
            // Check if user can submit a rating (rate limiting)
            if (!$user->canSubmitRating()) {
                $nextRatingTime = $user->getNextRatingTime();
                $message = 'You can only submit one rating per day.';
                
                if ($nextRatingTime) {
                    $message .= ' You can submit your next rating at ' . $nextRatingTime->format('Y-m-d H:i:s');
                }

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'next_rating_time' => $nextRatingTime?->toISOString(),
                ], 429);
            }

            // Prepare data for rating submission
            $ratingData = [
                'rating' => $request->input('rating'),
                'comment' => $request->input('comment'),
            ];

            // Get user's IP address and user agent for tracking
            $ipAddress = $request->ip();
            $userAgent = $request->userAgent();

            // Submit the rating using the User model method
            $rating = $user->submitRating($ratingData, $ipAddress, $userAgent);

            // Return success response with rating data
            return response()->json([
                'success' => true,
                'message' => 'Rating submitted successfully!',
                'data' => [
                    'id' => $rating->id,
                    'rating' => $rating->rating,
                    'rating_text' => $rating->rating_text,
                    'comment' => $rating->comment,
                    'submitted_at' => $rating->submitted_at->toISOString(),
                ],
            ], 201);

        } catch (ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            // Handle general errors
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's rating statistics.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserStats(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated.',
                ], 401);
            }

            $stats = [
                'total_ratings' => $user->ratings()->count(),
                'average_rating' => $user->getAverageRating(),
                'latest_rating' => $user->getLatestRating()?->only(['rating', 'comment', 'submitted_at']),
                'can_submit_rating' => $user->canSubmitRating(),
                'next_rating_time' => $user->getNextRatingTime()?->toISOString(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve rating statistics.',
            ], 500);
        }
    }

    /**
     * Get all ratings for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated.',
                ], 401);
            }

            $ratings = $user->ratings()
                ->latest('submitted_at')
                ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $ratings,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve ratings.',
            ], 500);
        }
    }

    /**
     * Admin view: Monitor all ratings (SYSTEM_ADMIN only via route middleware).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function adminIndex(Request $request)
    {
        // Base query with relations
        $query = Rating::with('user');

        // Optional filter: specific rating value
        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->input('rating'));
        }

        // Optional search across user name/email and comment
        if ($request->filled('q')) {
            $q = trim($request->input('q'));
            $query->where(function($sub) use ($q) {
                $sub->where('comment', 'like', "%$q%")
                    ->orWhereHas('user', function($u) use ($q) {
                        $u->where('first_name', 'like', "%$q%")
                          ->orWhere('last_name', 'like', "%$q%")
                          ->orWhere('email', 'like', "%$q%");
                    });
            });
        }

        // Latest first and paginate
        $ratings = $query->latest('submitted_at')->paginate(20)->withQueryString();

        // Basic aggregates
        $stats = [
            'total' => Rating::count(),
            'avg' => number_format((float) Rating::avg('rating'), 2),
            'last_24h' => Rating::where('submitted_at', '>=', now()->subDay())->count(),
        ];

        // If AJAX, return only the table partial for dynamic reloads
        if ($request->ajax()) {
            return view('admin.ratings._table', compact('ratings'));
        }

        return view('admin.ratings.index', compact('ratings', 'stats'));
    }
}
