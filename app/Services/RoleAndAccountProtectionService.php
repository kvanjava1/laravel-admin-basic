<?php

namespace App\Services;

use App\Models\User;
use App\Exceptions\ApiException;
use Spatie\Permission\Models\Role;

class RoleAndAccountProtectionService
{
    /**
     * Check if a role name is a protected system role.
     */
    public function isProtectedRole(string $roleName): bool
    {
        return in_array($roleName, config('protection.protected_roles', []));
    }

    /**
     * Check if a user (or email) is a protected system account.
     */
    public function isProtectedAccount($user): bool
    {
        $email = $user instanceof User ? $user->email : (string) $user;
        return in_array($email, config('protection.protected_accounts', []));
    }

    /**
     * Check if a user is generally system-protected (either account or role).
     */
    public function isSystemProtected(User $user): bool
    {
        return $this->isProtectedAccount($user) || $this->getProtectedRole($user) !== null;
    }

    /**
     * Get the first protected role name assigned to the user.
     */
    public function getProtectedRole(User $user): ?string
    {
        return $user->getRoleNames()->first(fn($role) => $this->isProtectedRole($role));
    }

    /**
     * Validate if a user can be banned.
     */
    public function validateUserBan(User $user): void
    {
        if ($this->isProtectedAccount($user)) {
            throw new ApiException("The account '{$user->email}' is system-protected and cannot be banned.", 403);
        }

        if ($role = $this->getProtectedRole($user)) {
            throw new ApiException("Users with the '{$role}' role cannot be banned.", 403);
        }
    }

    /**
     * Validate if a user status can be changed (Activate/Deactivate).
     */
    public function validateUserActiveDeactive(User $user): void
    {
        if ($this->isSystemProtected($user)) {
            throw new ApiException("This user is system-protected and their account status cannot be changed.", 403);
        }
    }

    /**
     * Validate if a user can be deleted.
     */
    public function validateUserDeletion(User $user): void
    {
        if ($this->isProtectedAccount($user)) {
            throw new ApiException("Super Administrator cannot be deleted.", 403);
        }

        if ($role = $this->getProtectedRole($user)) {
            $message = $role === 'Super Administrator' 
                ? "Super Administrator cannot be deleted." 
                : "Users with the '{$role}' role are system-protected and cannot be deleted.";
            throw new ApiException($message, 403);
        }
    }

    /**
     * Validate if the target user can be modified by the authenticated user.
     */
    public function validateUserModification(User $targetUser, User $authUser): void
    {
        if ($this->isProtectedAccount($targetUser) && $targetUser->id !== $authUser->id) {
            throw new ApiException("This system-protected account can only be edited by the owner.", 403);
        }

        if ($this->getProtectedRole($targetUser) && $targetUser->id !== $authUser->id) {
            throw new ApiException("Users with a system-protected role can only be edited by themselves.", 403);
        }
    }

    /**
     * Validate if a role can be modified or deleted.
     */
    public function validateRoleModification(Role $role): void
    {
        if ($this->isProtectedRole($role->name)) {
            $message = $role->name === 'Super Administrator'
                ? "Super Administrator cannot be deleted."
                : "The '{$role->name}' role is system-protected and cannot be modified or deleted.";
            throw new ApiException($message, 403);
        }
    }

    /**
     * Validate if a role can be assigned.
     */
    public function validateRoleAssignment(string $roleName, ?User $user = null): void
    {
        if ($this->isProtectedRole($roleName)) {
            // Allow if user already has it (maintaining state)
            if ($user && $user->hasRole($roleName)) {
                return;
            }

            throw new ApiException("The '{$roleName}' role cannot be assigned through the dashboard.", 422);
        }
    }

    /**
     * Boolean check for user modification authorization.
     */
    public function canModifyUser(User $targetUser, User $authUser): bool
    {
        if ($this->isProtectedAccount($targetUser)) {
            return $targetUser->id === $authUser->id;
        }

        return true;
    }

    /**
     * Boolean check for role modification authorization.
     */
    public function canModifyRole(Role $role): bool
    {
        return !$this->isProtectedRole($role->name);
    }
}
