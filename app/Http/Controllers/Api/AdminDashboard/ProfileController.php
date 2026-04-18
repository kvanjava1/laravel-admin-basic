<?php

namespace App\Http\Controllers\Api\AdminDashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminDashboard\Profile\UpdateProfileRequest;
use App\Traits\ApiResponse;
use App\Services\ProfileService;

class ProfileController extends Controller
{
    use ApiResponse;

    protected $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * Get the authenticated user's profile.
     */
    public function show(\Illuminate\Http\Request $request)
    {
        return $this->successResponse(
            $request->user()->load(['status', 'roles']),
            'Profile retrieved successfully'
        );
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(UpdateProfileRequest $request)
    {
        $user = $this->profileService->updateProfile(
            $request->user(),
            $request->validated()
        );

        return $this->successResponse(
            $user,
            'Profile updated successfully'
        );
    }
}
