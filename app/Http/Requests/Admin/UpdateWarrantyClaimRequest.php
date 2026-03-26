<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWarrantyClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:submitted,under_review,approved,rejected,in_repair,completed'],
            'resolution' => ['nullable', 'string'],
            'repair_id' => ['nullable', 'exists:repairs,id'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
