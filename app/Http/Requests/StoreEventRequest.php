<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_title' => ['required', 'string', 'max:255'],
            'event_description' => ['required', 'string', 'min:20'],
            'event_date' => ['required', 'date'],
            'event_time' => ['required', 'date_format:H:i'],
            'location' => ['required', 'string', 'max:255'],
            'visibility' => ['required', 'in:public,private'],
            'attendance_required' => ['nullable', 'boolean'],
            'attendance_time_in_starts_at' => ['nullable', 'date_format:H:i'],
            'attendance_time_in_ends_at' => ['nullable', 'date_format:H:i', 'after:attendance_time_in_starts_at'],
            'attendance_time_out_starts_at' => ['nullable', 'date_format:H:i', 'after:attendance_time_in_ends_at'],
            'attendance_time_out_ends_at' => ['nullable', 'date_format:H:i', 'after:attendance_time_out_starts_at'],
            'attendance_late_after' => ['nullable', 'date_format:H:i', 'after_or_equal:attendance_time_in_starts_at', 'before_or_equal:attendance_time_in_ends_at'],
            'department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')],
            'created_by_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
        ];
    }
}
