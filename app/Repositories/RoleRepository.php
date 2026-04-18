<?php

namespace App\Repositories;

use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class RoleRepository
{
    /**
     * Get all roles.
     */
    public function all()
    {
        return Role::with('permissions')->get();
    }

    /**
     * Get role options (id and name only).
     */
    public function getOptions()
    {
        return Role::select('id', 'name')->get();
    }

    /**
     * Get paginated roles.
     */
    public function paginate(int $perPage = 10, array $filters = [])
    {
        $query = Role::with('permissions')->latest();

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['permissions'])) {
            $permissionIds = is_array($filters['permissions']) ? $filters['permissions'] : [$filters['permissions']];
            $query->whereHas('permissions', function ($q) use ($permissionIds) {
                $q->whereIn('id', $permissionIds);
            });
        }

        // Date Range Filters: Created At
        if (!empty($filters['created_at_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_at_from']);
        }
        if (!empty($filters['created_at_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_at_to']);
        }

        // Date Range Filters: Updated At
        if (!empty($filters['updated_at_from'])) {
            $query->whereDate('updated_at', '>=', $filters['updated_at_from']);
        }
        if (!empty($filters['updated_at_to'])) {
            $query->whereDate('updated_at', '<=', $filters['updated_at_to']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Find a role by ID.
     */
    public function find(int $id)
    {
        return Role::with('permissions')->findOrFail($id);
    }

    /**
     * Create a new role.
     */
    public function create(array $data)
    {
        return Role::create([
            'name' => $data['name'],
            'guard_name' => 'web', // Default guard
        ]);
    }

    /**
     * Update an existing role.
     */
    public function update(Role $role, array $data)
    {
        $role->update([
            'name' => $data['name'],
        ]);
        return $role;
    }

    /**
     * Delete a role.
     */
    public function delete(Role $role)
    {
        return $role->delete();
    }
}
