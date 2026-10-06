<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
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
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'device_name' => ['required', 'string', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $email = $this->input('email');
        $name = $this->input('name');

        $this->merge([
            'email' => is_string($email) ? mb_strtolower(trim($email)) : $email,
            'name' => is_string($name) ? trim($name) : $name,
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.string' => 'O nome deve ser um texto.',
            'name.max' => 'O nome não pode ter mais de :max caracteres.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'O e-mail informado é inválido.',
            'email.unique' => 'Já existe um usuário cadastrado com este e-mail.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve possuir pelo menos :min caracteres.',
            'password.confirmed' => 'A confirmação da senha não corresponde.',
            'device_name.required' => 'O nome do dispositivo é obrigatório.',
            'device_name.max' => 'O nome do dispositivo não pode ter mais de :max caracteres.',
        ];
    }
}
