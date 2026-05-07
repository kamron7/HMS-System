<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::orderBy('name')->get();
        $roles = UserRole::cases();

        return view('users.index', compact('users', 'roles'));
    }

    public function create(): View
    {
        $roles = UserRole::cases();

        return view('users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:100'],
            'email'           => ['required', 'email', 'max:150', 'unique:users,email'],
            'password'        => ['required', 'string', 'min:8', 'confirmed'],
            'role'            => ['required', 'string', Rule::in(array_column(UserRole::cases(), 'value'))],
            'phone'           => ['nullable', 'string', 'max:30'],
            'passport_number' => ['nullable', 'string', 'max:50'],
            'birth_date'      => ['nullable', 'date', 'before:today'],
            'position'        => ['nullable', 'string', 'max:100'],
            'hire_date'       => ['nullable', 'date'],
            'avatar'          => ['nullable', 'image', 'max:2048'],
        ]);

        $data = $validated;
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        User::create([...$data, 'is_active' => true]);

        return redirect()->route('users.index')
            ->with('success', 'Сотрудник успешно добавлен.');
    }

    public function edit(User $user): View
    {
        $roles = UserRole::cases();

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:100'],
            'email'            => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'role'             => ['required', 'string', Rule::in(array_column(UserRole::cases(), 'value'))],
            'password'         => ['nullable', 'string', 'min:8', 'confirmed'],
            'telegram_chat_id'       => ['nullable', 'string', 'max:50'],
            'telegram_notifications' => ['nullable', 'array'],
            'telegram_notifications.*' => ['string'],
            'phone'                  => ['nullable', 'string', 'max:30'],
            'passport_number'  => ['nullable', 'string', 'max:50'],
            'birth_date'       => ['nullable', 'date', 'before:today'],
            'position'         => ['nullable', 'string', 'max:100'],
            'hire_date'        => ['nullable', 'date'],
            'avatar'           => ['nullable', 'image', 'max:2048'],
        ]);

        $data = $validated;
        if (empty($data['password'])) {
            unset($data['password']);
        }
        unset($data['password_confirmation']);

        // If the modal was submitted (sentinel present) but no boxes checked → empty array (mute all).
        // If the form was submitted without the sentinel → null (receive all, default).
        if ($request->has('tg_prefs_submitted')) {
            $data['telegram_notifications'] = $data['telegram_notifications'] ?? [];
        } else {
            unset($data['telegram_notifications']);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'Данные сотрудника обновлены.');
    }

    public function profile(): \Illuminate\View\View
    {
        $user = Auth::user();
        return view('profile', compact('user'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:100'],
            'phone'            => ['nullable', 'string', 'max:30'],
            'telegram_chat_id' => ['nullable', 'string', 'max:50'],
            'password'         => ['nullable', 'string', 'min:8', 'confirmed'],
            'avatar'           => ['nullable', 'image', 'max:2048'],
        ]);

        $data = collect($validated)->except(['password', 'avatar'])->toArray();

        if (!empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return redirect()->route('profile')->with('success', 'Профиль обновлён.');
    }

    public function toggleActive(User $user): RedirectResponse
    {
        if (Auth::id() === $user->id) {
            return redirect()->back()->with('error', 'Нельзя деактивировать себя');
        }

        $user->update(['is_active' => ! $user->is_active]);

        return redirect()->back()
            ->with('success', $user->is_active ? 'Сотрудник активирован.' : 'Сотрудник деактивирован.');
    }
}
