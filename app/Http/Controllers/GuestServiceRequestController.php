<?php

namespace App\Http\Controllers;

use App\Models\BookingCharge;
use App\Models\GuestServiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GuestServiceRequestController extends Controller
{
    public function index(): View
    {
        $status   = request('status', 'pending');
        $allowed  = ['pending', 'confirmed', 'declined'];
        $status   = in_array($status, $allowed) ? $status : 'pending';

        $requests = GuestServiceRequest::with(['booking.guest', 'room'])
            ->where('status', $status)
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('service-requests.index', compact('requests'));
    }

    public function confirm(GuestServiceRequest $serviceRequest): RedirectResponse
    {
        $serviceRequest->update(['status' => 'confirmed']);

        BookingCharge::create([
            'booking_id'  => $serviceRequest->booking_id,
            'description' => $serviceRequest->label,
            'category'    => 'other',
            'amount'      => $serviceRequest->price_per_unit * $serviceRequest->quantity,
            'created_by'  => auth()->id() ?? 1,
        ]);

        return back()->with('success', 'Запрос подтверждён и добавлен в счёт.');
    }

    public function decline(GuestServiceRequest $serviceRequest): RedirectResponse
    {
        $serviceRequest->update(['status' => 'declined']);

        return back()->with('success', 'Запрос отклонён.');
    }

    public function count(): JsonResponse
    {
        $count = GuestServiceRequest::where('status', 'pending')->count();

        return response()->json(['count' => $count]);
    }
}
