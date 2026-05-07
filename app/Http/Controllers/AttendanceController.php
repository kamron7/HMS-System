<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\User;
use App\Models\WorkerShift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $user = Auth::user();

        // For regular workers (receptionist, housekeeper, security): show only their own shift
        if (! in_array($user->role->value, ['owner', 'manager'])) {
            $myShift = WorkerShift::where('user_id', $user->id)
                ->where('status', 'open')
                ->orderByDesc('started_at')
                ->first();

            $todayLogs = AttendanceLog::where('user_id', $user->id)
                ->whereDate('logged_at', today())
                ->orderByDesc('logged_at')
                ->get();

            // Get this week's shifts
            $weekShifts = WorkerShift::where('user_id', $user->id)
                ->where('started_at', '>=', now()->startOfWeek())
                ->orderByDesc('started_at')
                ->limit(7)
                ->get();

            return view('attendance.index', [
                'myShift'    => $myShift,
                'todayLogs'  => $todayLogs,
                'weekShifts' => $weekShifts,
                'totalHoursWeek' => $weekShifts->sum('duration'),
            ]);
        }

        // For owner/manager: show all workers' status
        $allUsers = User::whereNotIn('role', ['owner'])->get();

        $workerStatuses = $allUsers->map(function ($u) {
            $openShift = WorkerShift::where('user_id', $u->id)
                ->where('status', 'open')
                ->orderByDesc('started_at')
                ->first();

            $todayLogs = AttendanceLog::where('user_id', $u->id)
                ->whereDate('logged_at', today())
                ->orderByDesc('logged_at')
                ->get();

            $weekShifts = WorkerShift::where('user_id', $u->id)
                ->where('started_at', '>=', now()->startOfWeek())
                ->orderByDesc('started_at')
                ->get();

            $lastAction = $todayLogs->first();
            $lastActionLabel = $lastAction ? $lastAction->typeLabel() : null;
            $lastActionColor = $lastAction ? $lastAction->typeColor() : null;

            return [
                'user'              => $u,
                'isOpen'            => (bool) $openShift,
                'shift'             => $openShift,
                'startedAt'         => $openShift?->started_at,
                'duration'          => $openShift?->duration_formatted ?? null,
                'lastAction'        => $lastActionLabel,
                'lastActionColor'   => $lastActionColor,
                'lastActionTime'    => $lastAction?->logged_at?->format('H:i'),
                'todayLogs'         => $todayLogs,
                'weekShifts'        => $weekShifts,
                'totalHoursWeek'    => $weekShifts->sum('duration'),
            ];
        });

        // Summary stats
        $onShiftCount = $workerStatuses->where('isOpen', true)->count();
        $offShiftCount = $workerStatuses->where('isOpen', false)->count();
        $totalHoursToday = $workerStatuses->sum(fn($w) => $w['shift']?->duration ?? 0);

        // Filter by shift status
        $filterStatus = $request->query('status');
        if ($filterStatus === 'on') {
            $workerStatuses = $workerStatuses->where('isOpen', true);
        } elseif ($filterStatus === 'off') {
            $workerStatuses = $workerStatuses->where('isOpen', false);
        }

        return view('attendance.index', [
            'workerStatuses'  => $workerStatuses->values(),
            'onShiftCount'    => $onShiftCount,
            'offShiftCount'   => $offShiftCount,
            'totalHoursToday' => $totalHoursToday,
            'isOwnerView'     => true,
        ]);
    }

    public function startShift(Request $request): JsonResponse
    {
        $user = Auth::user();
        $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        // Check if already has open shift
        $existing = WorkerShift::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if ($existing) {
            return response()->json(['error' => 'У вас уже есть открытая смена.'], 422);
        }

        $shift = WorkerShift::create([
            'user_id'    => $user->id,
            'shift_type' => 'regular',
            'started_at' => now(),
            'start_note' => $request->note,
            'status'     => 'open',
        ]);

        AttendanceLog::create([
            'user_id'         => $user->id,
            'worker_shift_id' => $shift->id,
            'type'            => 'check_in',
            'logged_at'       => now(),
            'ip_address'      => $request->ip(),
            'note'            => $request->note,
        ]);

        return response()->json([
            'success' => true,
            'shift'   => $shift,
        ]);
    }

    public function endShift(Request $request): JsonResponse
    {
        $user = Auth::user();
        $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $shift = WorkerShift::where('user_id', $user->id)
            ->where('status', 'open')
            ->orderByDesc('started_at')
            ->first();

        if (! $shift) {
            return response()->json(['error' => 'Нет активной смены.'], 422);
        }

        $shift->update([
            'ended_at' => now(),
            'end_note' => $request->note,
            'status'   => 'closed',
        ]);

        AttendanceLog::create([
            'user_id'         => $user->id,
            'worker_shift_id' => $shift->id,
            'type'            => 'check_out',
            'logged_at'       => now(),
            'ip_address'      => $request->ip(),
            'note'            => $request->note,
        ]);

        return response()->json([
            'success'  => true,
            'duration' => $shift->duration_formatted,
        ]);
    }

    public function toggleBreak(Request $request): JsonResponse
    {
        $user = Auth::user();

        $shift = WorkerShift::where('user_id', $user->id)
            ->where('status', 'open')
            ->orderByDesc('started_at')
            ->first();

        if (! $shift) {
            return response()->json(['error' => 'Нет активной смены.'], 422);
        }

        // Check last action type
        $lastLog = AttendanceLog::where('user_id', $user->id)
            ->where('worker_shift_id', $shift->id)
            ->orderByDesc('logged_at')
            ->first();

        $isOnBreak = $lastLog && $lastLog->type === 'break_start';

        $type = $isOnBreak ? 'break_end' : 'break_start';
        $note = $isOnBreak ? 'Перерыв закончился' : 'Начался перерыв';

        AttendanceLog::create([
            'user_id'         => $user->id,
            'worker_shift_id' => $shift->id,
            'type'            => $type,
            'logged_at'       => now(),
            'ip_address'      => $request->ip(),
            'note'            => $note,
        ]);

        return response()->json([
            'success' => true,
            'onBreak' => ! $isOnBreak,
        ]);
    }

    public function closeWorkerShift(Request $request, int $shiftId): JsonResponse
    {
        $user = Auth::user();

        // Only owner/manager can close other people's shifts
        abort_unless(in_array($user->role->value, ['owner', 'manager']), 403);

        $shift = WorkerShift::findOrFail($shiftId);

        if ($shift->status !== 'open') {
            return response()->json(['error' => 'Смена уже закрыта.'], 422);
        }

        $shift->update([
            'ended_at' => now(),
            'end_note' => 'Закрыто администратором',
            'status'   => 'closed',
        ]);

        AttendanceLog::create([
            'user_id'         => $shift->user_id,
            'worker_shift_id' => $shift->id,
            'type'            => 'check_out',
            'logged_at'       => now(),
            'ip_address'      => $request->ip(),
            'note'            => 'Закрыто администратором (' . $user->name . ')',
        ]);

        return response()->json(['success' => true]);
    }
}
