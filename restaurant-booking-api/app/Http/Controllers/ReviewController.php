<?php

namespace App\Http\Controllers;

use App\Services\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    protected $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
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
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json([
            'message' => 'Спасибо за ваш отзыв!',
            'review' => $result['review']
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        $result = $this->reviewService->updateReview($id, $request->user()->id, $data);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json([
            'message' => 'Отзыв успешно обновлен',
            'review' => $result['review']
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $result = $this->reviewService->deleteReview(
            $id,
            $request->user()->id,
            $request->user()->role === 'admin'
        );

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json([
            'message' => $result['message']
        ]);
    }

    public function myReviews(Request $request)
    {
        $reviews = \App\Models\Review::with(['restaurant', 'booking'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($reviews);
    }

    public function restaurantReviews($restaurantId)
    {
        $reviews = \App\Models\Review::with('user')
            ->where('restaurant_id', $restaurantId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $averageRating = \App\Models\Review::where('restaurant_id', $restaurantId)->avg('rating');

        return response()->json([
            'restaurant_id' => $restaurantId,
            'average_rating' => round($averageRating, 1) ?? 0,
            'total_reviews' => $reviews->total(),
            'reviews' => $reviews
        ]);
    }
}