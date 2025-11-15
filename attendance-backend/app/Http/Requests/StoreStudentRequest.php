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
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
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
            'class_id.required' => 'Class is required',
            'class_id.exists' => 'Selected class does not exist',
            'section_id.required' => 'Section is required',
            'section_id.exists' => 'Selected section does not exist',
            'photo.image' => 'File must be an image',
            'photo.mimes' => 'Image must be jpeg, png, or jpg format',
            'photo.max' => 'Image size must not exceed 2MB',
        ];
    }
}
