<?php

namespace App\Http\Controllers\Api\AdminDashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminDashboard\User\BanUserRequest;
use App\Models\User;
use App\Models\BanHistory;
use App\Models\UserStatus;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserBanController extends Controller
{
    use ApiResponse;

    /**
     * Display the ban history for a specific user.
     */
    public function index(User $user)
    {
        $history = $user->banHistories()
            ->with('admin')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse([
            'user' => $user->load('status'),
            'history' => $history
        ], 'Ban history retrieved successfully');
    }

    /**
     * Ban a user (permanent or temporary).
     */
    public function ban(BanUserRequest $request, User $user, \App\Services\RoleAndAccountProtectionService $protectionService)
    {
        $protectionService->validateUserBan($user);
        
        $bannedStatus = UserStatus::where('name', 'Banned')->firstOrFail();

        // 1. Log the history
        BanHistory::create([
            'user_id' => $user->id,
            'admin_id' => Auth::id(),
            'type' => $request->type,
            'action' => 'banned',
            'reason' => $request->reason,
            'expired_at' => $request->type === 'temporary' ? $request->expired_at : null,
        ]);

        // 2. Update User Status
        $user->update([
            'status_id' => $bannedStatus->id,
            'ban_expires_at' => $request->type === 'temporary' ? $request->expired_at : null,
        ]);

        return $this->successResponse($user->load('status'), 'User banned successfully');
    }

    /**
     * Restore a user to Active status.
     */
    public function unban(Request $request, User $user, \App\Services\RoleAndAccountProtectionService $protectionService)
    {
        $protectionService->validateUserModification($user, Auth::user());

        $request->validate([
            'reason' => 'required|string|min:5'
        ]);

        $activeStatus = UserStatus::where('name', 'Active')->firstOrFail();

        // 1. Log the history
        BanHistory::create([
            'user_id' => $user->id,
            'admin_id' => Auth::id(),
            'type' => 'permanent', // Unban action is permanent restore
            'action' => 'restored',
            'reason' => $request->reason,
        ]);

        // 2. Update User Status
        $user->update([
            'status_id' => $activeStatus->id,
            'ban_expires_at' => null,
        ]);

        return $this->successResponse($user->load('status'), 'User restored successfully');
    }

    /**
     * Activate a user (set status to Active).
     */
    public function activate(Request $request, User $user, \App\Services\RoleAndAccountProtectionService $protectionService)
    {
        $protectionService->validateUserActiveDeactive($user);

        $request->validate([
            'reason' => 'required|string|min:3'
        ]);

        $status = UserStatus::where('name', 'Active')->firstOrFail();

        BanHistory::create([
            'user_id' => $user->id,
            'admin_id' => Auth::id(),
            'type' => 'permanent',
            'action' => 'activated',
            'reason' => $request->reason,
        ]);

        $user->update([
            'status_id' => $status->id,
            'ban_expires_at' => null,
        ]);

        return $this->successResponse($user->load('status'), 'Account activated successfully');
    }

    /**
     * Deactivate a user (set status to Inactive).
     */
    public function deactivate(Request $request, User $user, \App\Services\RoleAndAccountProtectionService $protectionService)
    {
        $protectionService->validateUserActiveDeactive($user);

        $request->validate([
            'reason' => 'required|string|min:3'
        ]);

        $status = UserStatus::where('name', 'Inactive')->firstOrFail();

        BanHistory::create([
            'user_id' => $user->id,
            'admin_id' => Auth::id(),
            'type' => 'permanent',
            'action' => 'deactivated',
            'reason' => $request->reason,
        ]);

        $user->update([
            'status_id' => $status->id,
            'ban_expires_at' => null,
        ]);

        return $this->successResponse($user->load('status'), 'Account deactivated successfully');
    }
}
