<?php

namespace App\Services;

use App\Models\BanHistory;
use App\Models\User;
use App\Models\UserStatus;
use App\Exceptions\ApiException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserBanService
{
    public function __construct(
        protected RoleAndAccountProtectionService $protectionService
    ) {
    }

    /**
     * Get ban history for a user.
     */
    public function getBanHistory(User $user)
    {
        return $user->banHistories()
            ->with('admin')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Ban a user (permanent or temporary).
     */
    public function ban(User $user, array $data, User $admin): User
    {
        return DB::transaction(function () use ($user, $data, $admin) {
            $this->protectionService->validateUserBan($user);

            $bannedStatus = UserStatus::where('name', 'Banned')->firstOrFail();

            // 1. Log the audit trail
            BanHistory::create([
                'user_id' => $user->id,
                'admin_id' => $admin->id,
                'type' => $data['type'],
                'action' => 'banned',
                'reason' => $data['reason'],
                'expired_at' => $data['type'] === 'temporary' ? $data['expired_at'] : null,
            ]);

            // 2. Update user status
            $user->update([
                'status_id' => $bannedStatus->id,
                'ban_expires_at' => $data['type'] === 'temporary' ? $data['expired_at'] : null,
            ]);

            return $user->load('status');
        });
    }

    /**
     * Restore a user to Active status (unban).
     */
    public function unban(User $user, string $reason, User $admin): User
    {
        return DB::transaction(function () use ($user, $reason, $admin) {
            $this->protectionService->validateUserModification($user, $admin);

            $activeStatus = UserStatus::where('name', 'Active')->firstOrFail();

            // 1. Log the audit trail
            BanHistory::create([
                'user_id' => $user->id,
                'admin_id' => $admin->id,
                'type' => 'permanent',
                'action' => 'restored',
                'reason' => $reason,
            ]);

            // 2. Update user status
            $user->update([
                'status_id' => $activeStatus->id,
                'ban_expires_at' => null,
            ]);

            return $user->load('status');
        });
    }

    /**
     * Activate a user (set status to Active).
     */
    public function activate(User $user, string $reason, User $admin): User
    {
        return DB::transaction(function () use ($user, $reason, $admin) {
            $this->protectionService->validateUserActiveDeactive($user);

            $status = UserStatus::where('name', 'Active')->firstOrFail();

            // 1. Log the audit trail
            BanHistory::create([
                'user_id' => $user->id,
                'admin_id' => $admin->id,
                'type' => 'permanent',
                'action' => 'activated',
                'reason' => $reason,
            ]);

            // 2. Update user status
            $user->update([
                'status_id' => $status->id,
                'ban_expires_at' => null,
            ]);

            return $user->load('status');
        });
    }

    /**
     * Deactivate a user (set status to Inactive).
     */
    public function deactivate(User $user, string $reason, User $admin): User
    {
        return DB::transaction(function () use ($user, $reason, $admin) {
            $this->protectionService->validateUserActiveDeactive($user);

            $status = UserStatus::where('name', 'Inactive')->firstOrFail();

            // 1. Log the audit trail
            BanHistory::create([
                'user_id' => $user->id,
                'admin_id' => $admin->id,
                'type' => 'permanent',
                'action' => 'deactivated',
                'reason' => $reason,
            ]);

            // 2. Update user status
            $user->update([
                'status_id' => $status->id,
                'ban_expires_at' => null,
            ]);

            return $user->load('status');
        });
    }
}
