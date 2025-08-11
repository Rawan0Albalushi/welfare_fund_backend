<?php

namespace App\Http\Requests\Donations;

use Illuminate\Foundation\Http\FormRequest;

class CreateGiftDonationRequest extends FormRequest
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
            'recipient.name' => 'required|string|max:255',
            'recipient.phone' => 'required|string|regex:/^[0-9+\-\s()]+$/',
            'recipient.message' => 'nullable|string|max:1000',
            'sender.name' => 'nullable|string|max:255',
            'sender.phone' => 'nullable|string|regex:/^[0-9+\-\s()]+$/',
            'sender.hide_identity' => 'nullable|boolean',
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
            'recipient.name.required' => 'Recipient name is required.',
            'recipient.name.max' => 'Recipient name cannot exceed 255 characters.',
            'recipient.phone.required' => 'Recipient phone is required.',
            'recipient.phone.regex' => 'Please enter a valid recipient phone number.',
            'recipient.message.max' => 'Message cannot exceed 1000 characters.',
            'sender.name.max' => 'Sender name cannot exceed 255 characters.',
            'sender.phone.regex' => 'Please enter a valid sender phone number.',
        ];
    }
}
