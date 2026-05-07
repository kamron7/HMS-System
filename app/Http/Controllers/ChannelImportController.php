<?php

namespace App\Http\Controllers;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;

class ChannelImportController extends Controller
{
    public function index(): View
    {
        $sources = BookingSource::cases();
        return view('channel-import.index', compact('sources'));
    }

    public function preview(Request $request): View
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
            'source'   => ['required', 'string'],
        ]);

        $source  = $request->source;
        $file    = $request->file('csv_file');
        $rows    = [];
        $errors  = [];
        $preview = [];

        $handle = fopen($file->getRealPath(), 'r');
        $header = null;
        $lineNo = 0;

        while (($line = fgetcsv($handle, 1000, ',')) !== false) {
            $lineNo++;
            if ($lineNo === 1) {
                $header = array_map('trim', $line);
                continue;
            }
            if (empty(array_filter($line))) continue;

            $row = array_combine($header ?: [], array_pad($line, count($header ?: []), ''));

            $rowErrors = [];

            $checkIn  = $this->parseDate($row['check_in'] ?? '');
            $checkOut = $this->parseDate($row['check_out'] ?? '');

            if (! $checkIn) $rowErrors[] = 'Неверная дата заезда';
            if (! $checkOut) $rowErrors[] = 'Неверная дата выезда';
            if ($checkIn && $checkOut && $checkOut <= $checkIn) $rowErrors[] = 'Дата выезда раньше заезда';

            $firstName = trim($row['guest_first_name'] ?? $row['first_name'] ?? '');
            $lastName  = trim($row['guest_last_name']  ?? $row['last_name']  ?? '');
            if (empty($firstName)) $rowErrors[] = 'Не указано имя гостя';

            $roomNumber = trim($row['room_number'] ?? $row['room'] ?? '');
            $room = $roomNumber ? Room::where('number', $roomNumber)->first() : null;
            if ($roomNumber && ! $room) $rowErrors[] = "Номер «{$roomNumber}» не найден";

            $preview[] = [
                'line'       => $lineNo,
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'phone'      => trim($row['phone'] ?? ''),
                'email'      => trim($row['email'] ?? ''),
                'room_number'=> $roomNumber,
                'room_id'    => $room?->id,
                'check_in'   => $checkIn,
                'check_out'  => $checkOut,
                'adults'     => (int) ($row['adults'] ?? 1) ?: 1,
                'notes'      => trim($row['notes'] ?? ''),
                'errors'     => $rowErrors,
            ];
        }
        fclose($handle);

        // Store preview in session for confirmation step
        session(['channel_import_preview' => $preview, 'channel_import_source' => $source]);

        $sources = BookingSource::cases();

        return view('channel-import.preview', compact('preview', 'source', 'sources'));
    }

    public function import(Request $request): RedirectResponse
    {
        $preview = session('channel_import_preview', []);
        $source  = session('channel_import_source', 'other');

        if (empty($preview)) {
            return redirect()->route('channel-import.index')->with('error', 'Сессия истекла. Загрузите файл снова.');
        }

        $imported = 0;
        $skipped  = 0;

        foreach ($preview as $row) {
            if (! empty($row['errors'])) {
                $skipped++;
                continue;
            }
            if (! $row['room_id'] || ! $row['check_in'] || ! $row['check_out']) {
                $skipped++;
                continue;
            }

            // Find or create guest
            $guest = Guest::where('phone', $row['phone'])
                ->when($row['phone'], fn($q) => $q->where('phone', $row['phone']))
                ->first();

            if (! $guest) {
                $guest = Guest::firstOrCreate(
                    array_filter([
                        'first_name' => $row['first_name'],
                        'last_name'  => $row['last_name'] ?: null,
                        'phone'      => $row['phone'] ?: null,
                        'email'      => $row['email'] ?: null,
                    ]),
                );
            }

            $room   = Room::find($row['room_id']);
            $nights = Carbon::parse($row['check_in'])->diffInDays($row['check_out']);

            Booking::create([
                'room_id'        => $row['room_id'],
                'guest_id'       => $guest->id,
                'check_in_date'  => $row['check_in'],
                'check_out_date' => $row['check_out'],
                'adults'         => $row['adults'],
                'status'         => BookingStatus::Confirmed->value,
                'source'         => $source,
                'total_price'    => 0,
                'notes'          => $row['notes'] ?: null,
                'booking_ref'    => 'H-' . strtoupper(Str::random(6)),
                'created_by'     => auth()->id(),
            ]);

            $imported++;
        }

        session()->forget(['channel_import_preview', 'channel_import_source']);

        return redirect()->route('channel-import.index')
            ->with('success', "Импортировано: {$imported} бронирований." . ($skipped ? " Пропущено с ошибками: {$skipped}." : ''));
    }

    private function parseDate(string $value): ?string
    {
        if (empty($value)) return null;
        try {
            return Carbon::parse(trim($value))->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }
}
