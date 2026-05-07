<?php

namespace App\Http\Requests;

use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_id'        => ['required', 'integer', 'exists:rooms,id'],
            'guest_ids'      => ['required', 'array', 'min:1'],
            'guest_ids.*'    => ['integer', 'exists:guests,id'],
            'check_in_date'  => ['required', 'date', 'after_or_equal:today'],
            'check_in_time'  => ['nullable', 'date_format:H:i'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'check_out_time' => ['nullable', 'date_format:H:i'],
            'adults'         => ['required', 'integer', 'min:1', 'max:20'],
            'children'       => ['required', 'integer', 'min:0', 'max:20'],
            'notes'          => ['nullable', 'string', 'max:1000'],
            'promo_code'     => ['nullable', 'string', 'max:50'],
        ];
    }

    protected function passedValidation(): void
    {
        $room = Room::with('roomType')->find($this->room_id);
        if (! $room) return;

        $capacity = $room->roomType->capacity ?? 99;
        $total    = (int) $this->adults + (int) $this->children;

        if ($total > $capacity) {
            throw ValidationException::withMessages([
                'adults' => "Номер рассчитан максимум на {$capacity} чел. Указано: {$total}.",
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'guest_ids.required' => 'Выберите хотя бы одного гостя.',
            'guest_ids.min'      => 'Выберите хотя бы одного гостя.',
        ];
    }
}
