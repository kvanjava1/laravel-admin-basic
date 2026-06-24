<?php

namespace App\Services;

use App\Models\UserStatus;
use App\Repositories\UserRepository;
use App\Exceptions\ApiException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;
use App\Models\User;
use App\Traits\HandlesImageUploads;
use App\Services\RoleAndAccountProtectionService;
use Illuminate\Support\Facades\Auth;

class UserService
{
    use HandlesImageUploads;

    protected $userRepository;
    protected $protectionService;

    public function __construct(UserRepository $userRepository, RoleAndAccountProtectionService $protectionService)
    {
        $this->userRepository = $userRepository;
        $this->protectionService = $protectionService;
    }

    /**
     * Create a new user with business logic.
     */
    public function create(array $data)
    {
        // 0. Protect System Roles
        $this->protectionService->validateRoleAssignment($data['role']);

        // 1. Resolve status (default to Active if not provided)
        $statusId = $data['status_id'] ?? null;
        if (!$statusId) {
            $status = UserStatus::where('name', 'Active')->firstOrFail();
            $statusId = $status->id;
        } else {
            $status = UserStatus::findOrFail($statusId);
        }

        if ($status->name === 'Banned') {
            throw new ApiException('Users cannot be created with a Banned status.', 422);
        }

        // 2. Handle Avatar Storage & Cropping
        $avatarPath = null;
        if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
            $crops = isset($data['crop_x']) ? [
                'x' => $data['crop_x'],
                'y' => $data['crop_y'],
                'width' => $data['crop_width'],
                'height' => $data['crop_height'],
                'directory' => 'profile_pictures'
            ] : null;

            $avatarPath = $this->storeProfileImage($data['avatar'], $crops);
        }

        // 3. Prepare User Data
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status_id' => $statusId,
            'avatar' => $avatarPath,
        ];

        // 4. Delegate to Repository
        $user = $this->userRepository->create($userData);

        // 5. Sync Role
        $user->syncRoles($data['role']);

        return $user->load('roles');
    }

    /**
     * Update an existing user with business logic.
     */
    public function update(User $user, array $data)
    {
        // 0. Protect Primary Admin and System Role assignment
        $authUser = Auth::user();
        if ($authUser) {
            $this->protectionService->validateUserModification($user, $authUser);
        }

        $this->protectionService->validateRoleAssignment($data['role'], $user);

        // 1. Find status (if provided, otherwise keep existing)
        $statusId = $data['status_id'] ?? $user->status_id;

        // 2. Handle Avatar Storage & Cropping
        $avatarPath = $user->avatar; // Fallback to current avatar

        if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
            $crops = isset($data['crop_x']) ? [
                'x' => $data['crop_x'],
                'y' => $data['crop_y'],
                'width' => $data['crop_width'],
                'height' => $data['crop_height'],
                'directory' => 'profile_pictures'
            ] : null;

            $avatarPath = $this->storeProfileImage($data['avatar'], $crops);
        }

        // 3. Prepare User Data
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'status_id' => $statusId,
            'avatar' => $avatarPath,
        ];

        // 4. Handle Password Update
        if (!empty($data['password'])) {
            $userData['password'] = Hash::make($data['password']);
        }

        // 5. Delegate to Repository
        $user = $this->userRepository->update($user, $userData);

        // 6. Sync Role
        $user->syncRoles($data['role']);

        return $user->load('roles');
    }

    /**
     * Get paginated list of users.
     */
    public function getPaginatedUsers(int $perPage = 10, array $filters = [])
    {
        return $this->userRepository->paginate($perPage, $filters);
    }

    /**
     * Get user overview statistics.
     */
    public function getUserStats()
    {
        $stats = $this->userRepository->getStats();

        return [
            'total' => User::count(),
            'active' => $stats['Active'] ?? 0,
            'pending' => $stats['Pending'] ?? 0,
            'banned' => $stats['Banned'] ?? 0,
        ];
    }

    /**
     * Delete a user with protection and business logic.
     */
    public function delete(User $user)
    {
        // 0. Protect Primary Admin and System Role accounts from deletion
        $this->protectionService->validateUserDeletion($user);

        // 2. Delegate to Repository (performs soft delete)
        return $this->userRepository->delete($user);
    }
}
