<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class HallBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hall_id' => 'required|exists:halls,id',
            'guest_id' => 'nullable|exists:guests,id',
            'contact_name' => 'required|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'event_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'attendees' => 'required|integer|min:1',
            'paid_amount' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:pending,confirmed,cancelled,completed',
            'notes' => 'nullable|string',
        ];
    }
}
