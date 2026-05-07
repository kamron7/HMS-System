<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\GuestReview;
use App\Models\InAppNotification;
use App\Services\TelegramService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function show(Request $request, Booking $booking): View
    {
        abort_unless($request->hasValidSignature(), 403);

        $alreadyReviewed = GuestReview::where('booking_id', $booking->id)->exists();

        $booking->load(['guest', 'room.roomType']);

        return view('feedback.form', compact('booking', 'alreadyReviewed'));
    }

    public function store(Request $request, Booking $booking): View|RedirectResponse
    {
        abort_unless($request->hasValidSignature(), 403);

        if (GuestReview::where('booking_id', $booking->id)->exists()) {
            return redirect()->back()->with('error', 'Вы уже оставили отзыв.');
        }

        $data = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review = GuestReview::create([
            'booking_id'   => $booking->id,
            'guest_id'     => $booking->guest_id,
            'rating'       => $data['rating'],
            'comment'      => $data['comment'] ?? null,
            'submitted_at' => now(),
        ]);

        // Notify owner/manager of low ratings
        if ($review->rating <= 2) {
            $managers = \App\Models\User::whereIn('role', ['owner', 'manager'])->get();
            $guestName = $booking->guest?->full_name ?? 'Гость';
            foreach ($managers as $user) {
                InAppNotification::create([
                    'user_id'    => $user->id,
                    'type'       => 'low_review',
                    'title'      => "Низкая оценка ({$review->rating}★)",
                    'body'       => "Гость оставил отзыв на бронирование #{$booking->id}",
                    'url'        => route('bookings.show', $booking),
                    'reference'  => "low_review_{$review->id}",
                    'created_at' => now(),
                ]);
            }
            app(TelegramService::class)->sendTyped('feedback_negative', ['owner', 'manager'],
                "⭐ <b>Негативный отзыв ({$review->rating}/5)</b>\n" .
                "Гость: {$guestName}\n" .
                "Бронирование: #{$booking->id}"
            );
        }

        return view('feedback.thanks', compact('review'));
    }
}
