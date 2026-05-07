<?php

namespace App\Http\Requests;

use App\Models\BookingCharge;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChargeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:200'],
            'category'    => ['required', Rule::in(array_keys(BookingCharge::categories()))],
            'amount'      => ['required', 'numeric', 'min:0.01', 'max:99999999'],
        ];
    }
}
