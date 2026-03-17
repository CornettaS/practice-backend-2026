<?php

namespace App\Services;

use App\Models\Review;
use App\Models\Booking;

class ReviewService
{
    public function createReview($userId, $data)
    {
        $booking = Booking::with('table')->findOrFail($data['booking_id']);

        if ($booking->user_id !== $userId) {
            return [
                'success' => false,
                'message' => 'Вы можете оставлять отзывы только к своим бронированиям',
                'code' => 403
            ];
        }

        if ($booking->status !== 'completed') {
            return [
                'success' => false,
                'message' => 'Отзыв можно оставить только после посещения',
                'code' => 400
            ];
        }

        if ($booking->review) {
            return [
                'success' => false,
                'message' => 'Отзыв для этого бронирования уже существует',
                'code' => 400
            ];
        }

        $review = Review::create([
            'user_id' => $userId,
            'restaurant_id' => $booking->table->restaurant_id,
            'booking_id' => $data['booking_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null
        ]);

        return [
            'success' => true,
            'review' => $review->load('user')
        ];
    }

    public function updateReview($reviewId, $userId, $data)
    {
        $review = Review::findOrFail($reviewId);

        if ($review->user_id !== $userId) {
            return [
                'success' => false,
                'message' => 'Вы можете редактировать только свои отзывы',
                'code' => 403
            ];
        }

        $review->update($data);

        return [
            'success' => true,
            'review' => $review
        ];
    }

    public function deleteReview($reviewId, $userId)
    {
        $review = Review::findOrFail($reviewId);

        if ($review->user_id !== $userId) {
            return [
                'success' => false,
                'message' => 'Доступ запрещен',
                'code' => 403
            ];
        }

        $review->delete();

        return [
            'success' => true,
            'message' => 'Отзыв успешно удален'
        ];
    }
}