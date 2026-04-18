<?php

namespace App\Http\Requests\AdminDashboard\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\RoleAndAccountProtectionService;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(RoleAndAccountProtectionService $protectionService): bool
    {
        $targetUser = $this->route('user');
        $authUser = $this->user();

        if (!$targetUser || !$authUser) {
            return false;
        }

        return $protectionService->canModifyUser($targetUser, $authUser);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $userId,
            'password' => 'nullable|string|min:8|confirmed',
            'status_id' => 'nullable|integer|exists:user_statuses,id',
            'role' => 'required|string|exists:roles,name',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:6144',
            'crop_x' => 'nullable|numeric|min:0',
            'crop_y' => 'nullable|numeric|min:0',
            'crop_width' => 'nullable|numeric|min:1',
            'crop_height' => 'nullable|numeric|min:1',
        ];
    }
}
