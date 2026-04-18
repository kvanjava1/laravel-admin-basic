<?php

namespace App\Http\Requests\AdminDashboard\Role;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\RoleAndAccountProtectionService;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(RoleAndAccountProtectionService $protectionService): bool
    {
        $role = $this->route('role');
        if (!$role) {
            return false;
        }

        return $protectionService->canModifyRole($role);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $role = $this->route('role');
        $roleId = is_numeric($role) ? $role : $role->id;

        return [
            'name' => 'required|string|unique:roles,name,' . $roleId,
            'permissions' => 'nullable|array',
        ];
    }
}
