<?php

namespace App\n2_System\Identity\Http\Requests;

use App\n2_System\Access\Models\Role;
use App\n2_System\Identity\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $user instanceof User && ($this->user()?->can('update', $user) ?? false);
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
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->route('user')),
            ],
            'password' => ['nullable', 'string', Password::default(), 'confirmed'],
            'roles' => ['present', 'array'],
            'roles.*' => ['integer', 'distinct', 'exists:roles,id'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if (! $this->user()->isSuperAdmin() && Role::query()
                ->where('code', Role::SUPER_ADMIN)
                ->whereIn('id', (array) $this->input('roles', []))
                ->exists()) {
                $validator->errors()->add('roles', 'Системную роль может назначать только суперадминистратор.');
            }
        }];
    }
}
