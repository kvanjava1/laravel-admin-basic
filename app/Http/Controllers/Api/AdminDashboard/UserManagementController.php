<?php

namespace App\Http\Controllers\Api\AdminDashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminDashboard\User\StoreUserRequest;
use App\Http\Requests\AdminDashboard\User\UpdateUserRequest;
use App\Services\UserService;
use App\Models\UserStatus;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    use ApiResponse;

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'search',
            'status',
            'created_from',
            'created_to',
            'updated_from',
            'updated_to'
        ]);

        $users = $this->userService->getPaginatedUsers($request->input('per_page', 10), $filters);

        return $this->successResponse($users, 'Users retrieved successfully');
    }

    /**
     * Display user statistics.
     */
    public function stats()
    {
        $stats = $this->userService->getUserStats();
        return $this->successResponse($stats, 'User stats retrieved successfully');
    }

    /**
     * Display all available user statuses.
     */
    public function getStatuses()
    {
        return $this->successResponse(UserStatus::all(), 'User statuses retrieved successfully');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $user = $this->userService->create($request->validated());

        return $this->successResponse($user, 'User created successfully', 201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user, \App\Services\RoleAndAccountProtectionService $protectionService)
    {
        $user->load(['status', 'roles']);
        
        // Append protection status for frontend logic
        $user->is_protected = $protectionService->isProtectedAccount($user) || 
                             $user->getRoleNames()->contains(fn($role) => $protectionService->isProtectedRole($role));

        return $this->successResponse($user, 'User retrieved successfully');
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();
        
        // Remove status_id from basic update to enforce governance flow
        if (isset($data['status_id'])) {
            unset($data['status_id']);
        }

        $user = $this->userService->update($user, $data);

        return $this->successResponse($user, 'User updated successfully');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        $this->userService->delete($user);

        return $this->successResponse(null, 'User deleted successfully');
    }

    /**
     * Toggle ban status for the specified user.
     */
    public function toggleBan(User $user)
    {
        $updatedUser = $this->userService->toggleBan($user);
        $message = $updatedUser->status->name === 'Banned' ? 'User banned successfully' : 'User unbanned successfully';

        return $this->successResponse($updatedUser->load(['status', 'roles']), $message);
    }
}
