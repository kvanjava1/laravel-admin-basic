<?php

namespace App\Http\Controllers\Api\AdminDashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\RoleService;
use App\Traits\ApiResponse;
use Spatie\Permission\Models\Role;
use App\Http\Requests\AdminDashboard\Role\StoreRoleRequest;
use App\Http\Requests\AdminDashboard\Role\UpdateRoleRequest;

class RoleController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected RoleService $roleService
    ) {
    }

    /**
     * Display a listing of roles.
     */
    public function index(Request $request)
    {
        $roles = $this->roleService->getPaginatedRoles(
            $request->input('per_page', 10),
            $request->only(['search', 'permissions', 'created_at_from', 'created_at_to', 'updated_at_from', 'updated_at_to'])
        );

        return $this->successResponse($roles, 'Roles retrieved successfully');
    }

    /**
     * Get roles for selection dropdowns.
     */
    public function options()
    {
        $options = $this->roleService->getRoleOptions();
        return $this->successResponse($options, 'Role options retrieved successfully');
    }

    /**
     * Store a newly created role.
     */
    public function store(StoreRoleRequest $request)
    {
        $role = $this->roleService->create($request->validated());
        return $this->successResponse($role, 'Role created successfully', 201);
    }

    /**
     * Display the specified role.
     */
    public function show(int $id)
    {
        $role = $this->roleService->getRole($id);
        return $this->successResponse($role, 'Role retrieved successfully');
    }

    /**
     * Update the specified role.
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $role = $this->roleService->update($role, $request->validated());
        return $this->successResponse($role, 'Role updated successfully');
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role)
    {
        $this->roleService->delete($role);
        return $this->successResponse(null, 'Role deleted successfully');
    }
}
