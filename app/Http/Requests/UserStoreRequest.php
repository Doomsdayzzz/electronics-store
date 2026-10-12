<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'min:5', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:12'],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
        ];
    }
    public function messages(): array{
        return [
            'name.required' => 'Поле имя обязательно',
            'email.required' => 'Поле email обязательно для заполнения',
            'email.email' => 'Введен некорректный формат email адреса',
            'email.unique' => 'Такой email уже зарегистрирован',
        ];
    }
}
