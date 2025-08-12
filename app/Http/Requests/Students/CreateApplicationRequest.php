<?php

namespace App\Http\Requests\Students;

use Illuminate\Foundation\Http\FormRequest;

class CreateApplicationRequest extends FormRequest
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
            'program_id' => 'required|exists:programs,id',
            
            // Personal Information
            'personal' => 'required|array',
            'personal.full_name' => 'required|string|max:255',
            'personal.student_id' => 'required|string|max:50',
            'personal.email' => 'nullable|email',
            'personal.phone' => 'required|string|regex:/^[0-9+\-\s()]+$/',
            'personal.gender' => 'required|in:male,female',
            
            // Academic Information
            'academic' => 'required|array',
            'academic.university' => 'required|string|max:255',
            'academic.college' => 'required|string|max:255',
            'academic.major' => 'required|string|max:255',
            'academic.program' => 'required|string|max:255',
            'academic.academic_year' => 'required|integer|min:1|max:6',
            'academic.gpa' => 'required|numeric|min:0|max:4',
            
            // Financial Information
            'financial' => 'required|array',
            'financial.income_level' => 'required|in:low,medium,high',
            'financial.family_size' => 'required|in:1-3,4-6,7-9,10+',
            
            // Documents
            'id_card_image' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240', // 10MB max
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'program_id.required' => 'Program is required.',
            'program_id.exists' => 'Selected program does not exist.',
            
            // Personal Information
            'personal.required' => 'Personal information is required.',
            'personal.full_name.required' => 'Full name is required.',
            'personal.student_id.required' => 'Student ID is required.',
            'personal.phone.required' => 'Phone number is required.',
            'personal.phone.regex' => 'Please enter a valid phone number.',
            'personal.gender.required' => 'Gender is required.',
            'personal.gender.in' => 'Gender must be male or female.',
            'personal.email.email' => 'Please enter a valid email address.',
            
            // Academic Information
            'academic.required' => 'Academic information is required.',
            'academic.university.required' => 'University is required.',
            'academic.college.required' => 'College is required.',
            'academic.major.required' => 'Major is required.',
            'academic.program.required' => 'Program is required.',
            'academic.academic_year.required' => 'Academic year is required.',
            'academic.academic_year.integer' => 'Academic year must be a whole number.',
            'academic.academic_year.min' => 'Academic year must be at least 1.',
            'academic.academic_year.max' => 'Academic year cannot exceed 6.',
            'academic.gpa.required' => 'GPA is required.',
            'academic.gpa.numeric' => 'GPA must be a number.',
            'academic.gpa.min' => 'GPA must be at least 0.',
            'academic.gpa.max' => 'GPA cannot exceed 4.',
            
            // Financial Information
            'financial.required' => 'Financial information is required.',
            'financial.income_level.required' => 'Income level is required.',
            'financial.income_level.in' => 'Income level must be low, medium, or high.',
            'financial.family_size.required' => 'Family size is required.',
            'financial.family_size.in' => 'Family size must be 1-3, 4-6, 7-9, or 10+.',
            
            // Documents
            'id_card_image.file' => 'ID card image must be a file.',
            'id_card_image.mimes' => 'ID card image must be a JPG, JPEG, PNG, or PDF file.',
            'id_card_image.max' => 'ID card image must not exceed 10MB.',
        ];
    }
}
