<?php

namespace App\Http\Controllers;

use App\Mail\CustomUserMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class UserMailController extends Controller
{
    public function index(): View
    {
        $users = User::whereNotNull('email')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('users.mail', compact('users'));
    }

    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_ids'   => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'subject'    => ['required', 'string', 'max:200'],
            'body'       => ['required', 'string', 'max:100000'],
        ]);

        $users = User::whereIn('id', $validated['user_ids'])
            ->whereNotNull('email')
            ->get();

        $sent   = 0;
        $failed = 0;

        foreach ($users as $user) {
            try {
                Mail::to($user->email)
                    ->send(new CustomUserMail($user, $validated['subject'], $validated['body']));
                $sent++;
            } catch (\Throwable) {
                $failed++;
            }
        }

        $message = "Отправлено: {$sent}";
        if ($failed > 0) {
            $message .= ", не удалось: {$failed}";
        }

        return redirect()->route('users.mail')->with('success', $message);
    }
}
