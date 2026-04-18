<?php

namespace App\Http\Requests\AdminDashboard\User;

use Illuminate\Foundation\Http\FormRequest;

use App\Services\RoleAndAccountProtectionService;

class BanUserRequest extends FormRequest
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
        return [
            'type' => 'required|string|in:permanent,temporary',
            'reason' => 'required|string|min:5|max:1000',
            'expired_at' => 'required_if:type,temporary|nullable|date|after:now',
        ];
    }
}
