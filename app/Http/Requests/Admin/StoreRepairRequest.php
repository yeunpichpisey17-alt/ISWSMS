<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreRepairRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'product_id' => ['nullable', 'exists:products,id'],
            'serial_number' => ['nullable', 'string'],
            'device_name' => ['required', 'string', 'max:255'],
            'device_brand' => ['nullable', 'string', 'max:255'],
            'device_model' => ['nullable', 'string', 'max:255'],
            'issue_description' => ['required', 'string'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'estimated_completion' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
