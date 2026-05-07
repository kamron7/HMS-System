<?php

namespace App\Http\Controllers;

use App\Mail\CustomGuestMail;
use App\Models\Guest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class GuestMailController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->query('filter', 'all');

        $guests = Guest::whereNotNull('email')
            ->when($filter === 'active', fn($q) => $q->whereHas('bookings', function ($q) {
                $q->whereIn('status', ['pending', 'confirmed', 'checked_in']);
            }))
            ->when($filter === 'past', fn($q) => $q->whereHas('bookings', function ($q) {
                $q->where('status', 'checked_out');
            }))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('guests.mail', compact('guests', 'filter'));
    }

    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'guest_ids' => ['required', 'array', 'min:1'],
            'guest_ids.*' => ['integer', 'exists:guests,id'],
            'subject'   => ['required', 'string', 'max:200'],
            'body'      => ['required', 'string', 'max:100000'],
        ]);

        $guests = Guest::whereIn('id', $validated['guest_ids'])
            ->whereNotNull('email')
            ->get();

        $sent   = 0;
        $failed = 0;

        foreach ($guests as $guest) {
            try {
                Mail::to($guest->email)
                    ->send(new CustomGuestMail($guest, $validated['subject'], $validated['body']));
                $sent++;
            } catch (\Throwable) {
                $failed++;
            }
        }

        $message = "Отправлено: {$sent}";
        if ($failed > 0) {
            $message .= ", не удалось: {$failed}";
        }

        return redirect()->route('guests.mail')->with('success', $message);
    }
}
