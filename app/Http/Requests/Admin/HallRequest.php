<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class HallRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:halls,code,' . $this->hall,
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'base_price' => 'required|numeric|min:0',
            'size' => 'required|in:small,medium,large',
            'type' => 'required|in:conference,banquet,meeting',
            'is_active' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Hall name is required',
            'code.required' => 'Hall code is required',
            'code.unique' => 'Hall code must be unique',
            'capacity.required' => 'Capacity is required',
            'capacity.min' => 'Capacity must be at least 1',
            'base_price.required' => 'Base price is required',
            'base_price.min' => 'Base price must be at least 0',
            'size.required' => 'Size is required',
            'type.required' => 'Type is required',
        ];
    }
}
