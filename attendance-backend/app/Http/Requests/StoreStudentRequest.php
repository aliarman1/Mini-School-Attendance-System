<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'student_id' => 'required|string|unique:students,student_id|max:50',
            'class' => 'required|string|max:50',
            'section' => 'required|string|max:50',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
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
            'name.required' => 'Student name is required',
            'student_id.required' => 'Student ID is required',
            'student_id.unique' => 'This student ID already exists',
            'class.required' => 'Class is required',
            'section.required' => 'Section is required',
            'photo.image' => 'File must be an image',
            'photo.mimes' => 'Image must be jpeg, png, or jpg format',
            'photo.max' => 'Image size must not exceed 2MB',
        ];
    }
}
