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
            'personal' => 'required|array',
            'personal.full_name' => 'required|string|max:255',
            'personal.national_id' => 'required|string|max:50',
            'personal.date_of_birth' => 'required|date',
            'personal.gender' => 'required|in:male,female',
            'personal.address' => 'required|string|max:500',
            'personal.phone' => 'required|string|regex:/^[0-9+\-\s()]+$/',
            'personal.email' => 'nullable|email',
            
            'academic' => 'required|array',
            'academic.university' => 'required|string|max:255',
            'academic.faculty' => 'required|string|max:255',
            'academic.department' => 'required|string|max:255',
            'academic.student_id' => 'required|string|max:50',
            'academic.gpa' => 'required|numeric|min:0|max:4',
            'academic.academic_year' => 'required|integer|min:1|max:6',
            
            'financial' => 'required|array',
            'financial.family_income' => 'required|numeric|min:0',
            'financial.family_size' => 'required|integer|min:1',
            'financial.father_occupation' => 'nullable|string|max:255',
            'financial.mother_occupation' => 'nullable|string|max:255',
            'financial.monthly_expenses' => 'required|numeric|min:0',
            'financial.other_sources' => 'nullable|string|max:500',
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
            'personal.required' => 'Personal information is required.',
            'personal.full_name.required' => 'Full name is required.',
            'personal.national_id.required' => 'National ID is required.',
            'personal.date_of_birth.required' => 'Date of birth is required.',
            'personal.gender.required' => 'Gender is required.',
            'personal.address.required' => 'Address is required.',
            'personal.phone.required' => 'Phone number is required.',
            'personal.phone.regex' => 'Please enter a valid phone number.',
            'personal.email.email' => 'Please enter a valid email address.',
            
            'academic.required' => 'Academic information is required.',
            'academic.university.required' => 'University is required.',
            'academic.faculty.required' => 'Faculty is required.',
            'academic.department.required' => 'Department is required.',
            'academic.student_id.required' => 'Student ID is required.',
            'academic.gpa.required' => 'GPA is required.',
            'academic.gpa.numeric' => 'GPA must be a number.',
            'academic.gpa.min' => 'GPA must be at least 0.',
            'academic.gpa.max' => 'GPA cannot exceed 4.',
            'academic.academic_year.required' => 'Academic year is required.',
            'academic.academic_year.integer' => 'Academic year must be a whole number.',
            'academic.academic_year.min' => 'Academic year must be at least 1.',
            'academic.academic_year.max' => 'Academic year cannot exceed 6.',
            
            'financial.required' => 'Financial information is required.',
            'financial.family_income.required' => 'Family income is required.',
            'financial.family_income.numeric' => 'Family income must be a number.',
            'financial.family_income.min' => 'Family income must be at least 0.',
            'financial.family_size.required' => 'Family size is required.',
            'financial.family_size.integer' => 'Family size must be a whole number.',
            'financial.family_size.min' => 'Family size must be at least 1.',
            'financial.monthly_expenses.required' => 'Monthly expenses are required.',
            'financial.monthly_expenses.numeric' => 'Monthly expenses must be a number.',
            'financial.monthly_expenses.min' => 'Monthly expenses must be at least 0.',
        ];
    }
}
