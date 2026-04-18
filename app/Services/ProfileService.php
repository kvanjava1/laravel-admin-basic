<?php

namespace App\Services;

use App\Models\User;
use App\Traits\HandlesImageUploads;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;

class ProfileService
{
    use HandlesImageUploads;

    /**
     * Update the authenticated user's profile.
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateProfile(User $user, array $data)
    {
        // 1. Handle Avatar Update
        if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
            $crops = isset($data['crop_x']) ? [
                'x' => $data['crop_x'],
                'y' => $data['crop_y'],
                'width' => $data['crop_width'],
                'height' => $data['crop_height'],
                'directory' => 'profile_pictures'
            ] : null;

            $avatarPath = $this->storeProfileImage($data['avatar'], $crops);
            if ($avatarPath) {
                $user->avatar = $avatarPath;
            }
        }

        // 2. Update Basic Info
        $user->name = $data['name'];
        $user->email = $data['email'];

        // 3. Handle Password Update
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return $user->load(['status', 'roles']);
    }
}
