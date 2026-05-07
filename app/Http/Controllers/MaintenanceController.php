<?php

namespace App\Http\Controllers;

use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceStatus;
use App\Http\Requests\StoreMaintenanceRequest;
use App\Models\MaintenanceRequest;
use App\Models\Room;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    public function index(): View
    {
        $requests = MaintenanceRequest::with(['room', 'assignee', 'creator', 'guest', 'booking'])
            ->orderByRaw("CASE status WHEN 'open' THEN 0 WHEN 'in_progress' THEN 1 ELSE 2 END")
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 0 WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->get()
            ->groupBy(fn($r) => $r->status->value);

        $staff = User::where('is_active', true)->orderBy('name')->get();

        return view('maintenance.index', compact('requests', 'staff'));
    }

    public function create(): View
    {
        $rooms      = Room::orderBy('number')->get();
        $priorities = MaintenancePriority::cases();

        return view('maintenance.create', compact('rooms', 'priorities'));
    }

    public function store(StoreMaintenanceRequest $request): RedirectResponse
    {
        $req = MaintenanceRequest::create([
            'room_id'     => $request->room_id,
            'title'       => $request->title,
            'description' => $request->description,
            'priority'    => $request->priority,
            'status'      => MaintenanceStatus::Open->value,
            'created_by'  => auth()->id(),
        ]);

        // In-app notifications
        $req->load('room');
        app(NotificationService::class)->notifyNewMaintenance($req);

        $room     = $req->room;
        $priority = MaintenancePriority::from($request->priority)->label();
        $roles    = $request->priority === 'urgent' ? ['owner', 'manager'] : ['manager'];
        $icon     = match($request->priority) {
            'urgent' => '🚨', 'high' => '🔴', 'normal' => '🔧', default => '🔵',
        };
        app(TelegramService::class)->sendTyped('maintenance_new', $roles,
            "{$icon} <b>Заявка на обслуживание</b> [{$priority}]\n" .
            "Номер: " . ($room?->number ?? '—') . "\n" .
            "Тема: {$request->title}"
        );

        return redirect()->route('maintenance.index')
            ->with('success', 'Заявка создана.');
    }

    public function show(MaintenanceRequest $maintenance): View
    {
        $maintenance->load(['room', 'assignee', 'creator', 'guest', 'booking']);

        return view('maintenance.show', compact('maintenance'));
    }

    public function edit(MaintenanceRequest $maintenance): View
    {
        $rooms      = Room::orderBy('number')->get();
        $priorities = MaintenancePriority::cases();
        $staff      = User::where('is_active', true)->orderBy('name')->get();

        return view('maintenance.edit', compact('maintenance', 'rooms', 'priorities', 'staff'));
    }

    public function update(Request $request, MaintenanceRequest $maintenance): RedirectResponse
    {
        $request->validate([
            'title'       => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'priority'    => ['required', 'string'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $oldAssignedTo = $maintenance->assigned_to;

        $maintenance->update([
            'title'       => $request->title,
            'description' => $request->description,
            'priority'    => $request->priority,
            'assigned_to' => $request->assigned_to ?: null,
        ]);

        // Notify newly assigned staff member
        if ($request->assigned_to && (int) $request->assigned_to !== (int) $oldAssignedTo) {
            $maintenance->load('room');
            app(NotificationService::class)->notifyMaintenanceAssigned($maintenance);
        }

        return redirect()->route('maintenance.show', $maintenance)
            ->with('success', 'Заявка обновлена.');
    }

    public function resolve(MaintenanceRequest $maintenance): RedirectResponse
    {
        $maintenance->update([
            'status'      => MaintenanceStatus::Resolved->value,
            'resolved_at' => now(),
        ]);

        return redirect()->route('maintenance.index')
            ->with('success', 'Заявка решена.');
    }

    public function updateStatus(Request $request, MaintenanceRequest $maintenance): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'string'],
        ]);

        $status = MaintenanceStatus::from($request->status);

        $data = ['status' => $status->value];
        if ($status === MaintenanceStatus::Resolved) {
            $data['resolved_at'] = now();
        }

        $maintenance->update($data);

        return response()->json(['ok' => true]);
    }
}
