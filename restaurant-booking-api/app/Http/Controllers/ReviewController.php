<?php

namespace App\Http\Controllers;

use App\Services\ReviewService;
use Illuminate\Http\Request;
use App\Models\Review;

class ReviewController extends Controller
{
    protected $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }
    
 
    private function checkReviewAccess($review, $user)
    {
        if ($review->user_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'Доступ запрещен');
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        $result = $this->reviewService->createReview($request->user()->id, $data);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Спасибо за ваш отзыв!',
            'data' => $result['review']
        ], 201);
    }


    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        $review = Review::findOrFail($id);
        $this->checkReviewAccess($review, $request->user());

        $result = $this->reviewService->updateReview($id, $request->user()->id, $data);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Отзыв успешно обновлен',
            'data' => $result['review']
        ]);
    }


    public function destroy(Request $request, $id)
    {
        $review = Review::findOrFail($id);
        $this->checkReviewAccess($review, $request->user());

        $result = $this->reviewService->deleteReview($id, $request->user()->id);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message']
        ]);
    }


    public function myReviews(Request $request)
    {
        $reviews = Review::with(['restaurant', 'booking'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    public function restaurantReviews($restaurantId)
    {
        $reviews = Review::with('user')
            ->where('restaurant_id', $restaurantId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $averageRating = Review::where('restaurant_id', $restaurantId)->avg('rating');
        $totalReviews = Review::where('restaurant_id', $restaurantId)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'restaurant_id' => $restaurantId,
                'average_rating' => round($averageRating, 1) ?? 0,
                'total_reviews' => $totalReviews,
                'reviews' => $reviews
            ]
        ]);
    }
}