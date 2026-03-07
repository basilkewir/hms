<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PackageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:packages,code,' . $this->package,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'included_features' => 'nullable|array',
            'optional_features' => 'nullable|array',
            'is_active' => 'boolean',
            'is_available' => 'boolean',
            'max_bookings' => 'nullable|integer|min:0',
            'min_guests' => 'nullable|integer|min:0',
            'max_guests' => 'nullable|integer|min:0',
            'duration_hours' => 'nullable|integer|min:0',
            'hall_ids' => 'nullable|array',
            'hall_ids.*' => 'exists:halls,id',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Package name is required',
            'code.required' => 'Package code is required',
            'code.unique' => 'Package code must be unique',
            'price.required' => 'Price is required',
            'price.min' => 'Price must be at least 0',
        ];
    }
}
