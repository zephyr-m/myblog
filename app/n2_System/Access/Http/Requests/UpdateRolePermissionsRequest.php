<?php

namespace App\n2_System\Access\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRolePermissionsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->canDo('role.update') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'roles' => ['present', 'array'],
            'roles.*' => ['array'],
            'roles.*.*' => ['integer', 'distinct', 'exists:permissions,id'],
        ];
    }

    /** @return array<int, list<int>> */
    public function matrix(): array
    {
        $matrix = [];

        foreach ($this->array('roles') as $roleId => $permissionIds) {
            if (! is_array($permissionIds)) {
                continue;
            }

            $matrix[(int) $roleId] = array_values(array_map(
                fn (mixed $permissionId): int => (int) $permissionId,
                $permissionIds,
            ));
        }

        return $matrix;
    }
}
