<?php

namespace App\Services;

use App\Repositories\RoleRepository;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Services\RoleAndAccountProtectionService;

class RoleService
{
    protected $roleRepository;
    protected $protectionService;

    public function __construct(RoleRepository $roleRepository, RoleAndAccountProtectionService $protectionService)
    {
        $this->roleRepository = $roleRepository;
        $this->protectionService = $protectionService;
    }

    /**
     * Get paginated roles.
     */
    public function getPaginatedRoles(int $perPage = 10, array $filters = [])
    {
        return $this->roleRepository->paginate($perPage, $filters);
    }

    /**
     * Get role options for selection lists.
     */
    public function getRoleOptions()
    {
        return $this->roleRepository->getOptions();
    }

    /**
     * Create a new role and sync permissions.
     */
    public function create(array $data)
    {
        $role = $this->roleRepository->create($data);

        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->load('permissions');
    }

    /**
     * Update an existing role and sync permissions.
     */
    public function update(Role $role, array $data)
    {
        $this->protectionService->validateRoleModification($role);

        $role = $this->roleRepository->update($role, $data);

        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->load('permissions');
    }

    /**
     * Delete an existing role with protection check.
     */
    public function delete(Role $role)
    {
        $this->protectionService->validateRoleModification($role);

        return $this->roleRepository->delete($role);
    }

    /**
     * Get all available permissions.
     */
    public function getAllPermissions()
    {
        return Permission::all();
    }

    /**
     * Get a single role by ID.
     */
    public function getRole(int $id)
    {
        return $this->roleRepository->find($id);
    }
}
