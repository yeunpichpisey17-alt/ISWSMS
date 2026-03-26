<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRepairRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'diagnosis' => ['nullable', 'string'],
            'resolution' => ['nullable', 'string'],
            'status' => ['required', 'in:received,diagnosing,waiting_parts,in_repair,completed,delivered,cancelled'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'actual_cost' => ['nullable', 'numeric', 'min:0'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'estimated_completion' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
