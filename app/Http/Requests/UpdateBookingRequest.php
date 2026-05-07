<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_id'        => ['required', 'integer', 'exists:rooms,id'],
            'guest_id'       => ['required', 'integer', 'exists:guests,id'],
            'check_in_date'  => ['required', 'date'],
            'check_in_time'  => ['nullable', 'date_format:H:i'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'check_out_time' => ['nullable', 'date_format:H:i'],
            'adults'         => ['required', 'integer', 'min:1', 'max:20'],
            'children'       => ['required', 'integer', 'min:0', 'max:20'],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ];
    }
}
