<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreWarrantyClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'sale_id' => ['nullable', 'exists:sales,id'],
            'product_id' => ['required', 'exists:products,id'],
            'serial_number' => ['nullable', 'string'],
            'issue_description' => ['required', 'string'],
            'warranty_start' => ['required', 'date'],
            'warranty_end' => ['required', 'date', 'after:warranty_start'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
