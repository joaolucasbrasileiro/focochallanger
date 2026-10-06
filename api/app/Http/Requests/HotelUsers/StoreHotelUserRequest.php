<?php

namespace App\Http\Requests\HotelUsers;

use App\Enums\UserRole;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreHotelUserRequest extends FormRequest
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
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::enum(UserRole::class)],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $user = User::query()->where('email', $this->string('email')->toString())->first();

                if ($user === null) {
                    if (! $this->filled('name')) {
                        $validator->errors()->add('name', 'O nome é obrigatório para um novo usuário.');
                    }

                    if (! $this->filled('password')) {
                        $validator->errors()->add('password', 'A senha é obrigatória para um novo usuário.');
                    }

                    return;
                }

                $hotel = $this->route('hotel');

                if ($hotel instanceof Hotel && $user->membershipFor($hotel) !== null) {
                    $validator->errors()->add('email', 'Este usuário já pertence ao hotel informado.');
                }
            },
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
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'O e-mail informado é inválido.',
            'name.string' => 'O nome deve ser um texto.',
            'password.min' => 'A senha deve possuir pelo menos :min caracteres.',
            'password.confirmed' => 'A confirmação da senha não corresponde.',
            'role.required' => 'O papel do usuário é obrigatório.',
            'role.enum' => 'O papel informado é inválido.',
        ];
    }
}
