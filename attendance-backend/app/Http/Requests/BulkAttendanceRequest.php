<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date' => 'required|date',
            'recorded_by' => 'required|string|max:255',
            'attendances' => 'required|array|min:1',
            'attendances.*.student_id' => 'required|exists:students,id',
            'attendances.*.status' => 'required|in:present,absent,late',
            'attendances.*.note' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'date.required' => 'Attendance date is required',
            'date.date' => 'Invalid date format',
            'recorded_by.required' => 'Recorder name is required',
            'attendances.required' => 'Attendance records are required',
            'attendances.array' => 'Attendance records must be an array',
            'attendances.min' => 'At least one attendance record is required',
            'attendances.*.student_id.required' => 'Student ID is required for each attendance',
            'attendances.*.student_id.exists' => 'Student does not exist',
            'attendances.*.status.required' => 'Status is required for each attendance',
            'attendances.*.status.in' => 'Status must be present, absent, or late',
            'attendances.*.note.max' => 'Note must not exceed 500 characters',
        ];
    }
}
