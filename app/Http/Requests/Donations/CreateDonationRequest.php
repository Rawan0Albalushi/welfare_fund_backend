<?php

namespace App\Http\Requests\Donations;

use Illuminate\Foundation\Http\FormRequest;

class CreateDonationRequest extends FormRequest
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
            'amount' => 'required|numeric|min:1',
            'donor_name' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:1000',
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
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a number.',
            'amount.min' => 'Amount must be at least 1.',
            'donor_name.max' => 'Donor name cannot exceed 255 characters.',
            'note.max' => 'Note cannot exceed 1000 characters.',
        ];
    }
}
