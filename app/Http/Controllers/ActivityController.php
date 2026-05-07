<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->orderByDesc('created_at');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', 'like', $request->action . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(50)->withQueryString();

        $users = User::orderBy('name')->get();

        $actionGroups = [
            'booking'     => 'Бронирования',
            'payment'     => 'Оплаты',
            'guest'       => 'Гости',
            'expense'     => 'Расходы',
            'room'        => 'Номера',
            'user'        => 'Сотрудники',
            'maintenance' => 'Техслужба',
        ];

        return view('activity.index', compact('logs', 'users', 'actionGroups'));
    }
}
