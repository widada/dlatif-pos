<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^(\+?62|08)\d{8,12}$/', Rule::unique('customers', 'phone')->ignore($this->route('customer'))],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Format nomor HP tidak valid. Gunakan format 08xx atau +628xx.',
            'phone.unique' => 'Nomor HP sudah dipakai customer lain.',
            'name.min' => 'Nama minimal 2 karakter.',
        ];
    }
}
