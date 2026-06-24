<?php

namespace App\Http\Controllers\Api\AdminDashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminDashboard\User\BanUserRequest;
use App\Models\User;
use App\Services\UserBanService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserBanController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected UserBanService $userBanService
    ) {
    }

    /**
     * Display the ban history for a specific user.
     */
    public function index(User $user)
    {
        $history = $this->userBanService->getBanHistory($user);

        return $this->successResponse([
            'user' => $user->load('status'),
            'history' => $history
        ], 'Ban history retrieved successfully');
    }

    /**
     * Ban a user (permanent or temporary).
     */
    public function ban(BanUserRequest $request, User $user)
    {
        $updatedUser = $this->userBanService->ban($user, $request->validated(), Auth::user());

        return $this->successResponse($updatedUser, 'User banned successfully');
    }

    /**
     * Restore a user to Active status.
     */
    public function unban(Request $request, User $user)
    {
        $request->validate([
            'reason' => 'required|string|min:5'
        ]);

        $updatedUser = $this->userBanService->unban($user, $request->reason, Auth::user());

        return $this->successResponse($updatedUser, 'User restored successfully');
    }

    /**
     * Activate a user (set status to Active).
     */
    public function activate(Request $request, User $user)
    {
        $request->validate([
            'reason' => 'required|string|min:3'
        ]);

        $updatedUser = $this->userBanService->activate($user, $request->reason, Auth::user());

        return $this->successResponse($updatedUser, 'Account activated successfully');
    }

    /**
     * Deactivate a user (set status to Inactive).
     */
    public function deactivate(Request $request, User $user)
    {
        $request->validate([
            'reason' => 'required|string|min:3'
        ]);

        $updatedUser = $this->userBanService->deactivate($user, $request->reason, Auth::user());

        return $this->successResponse($updatedUser, 'Account deactivated successfully');
    }
}
