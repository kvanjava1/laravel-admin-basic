<?php

namespace App\Http\Controllers\Api\AdminDashboard;

use App\Http\Controllers\Controller;
use App\Services\RoleService;
use App\Traits\ApiResponse;

class PermissionController extends Controller
{
    use ApiResponse;

    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * Display a listing of all permissions.
     */
    public function index()
    {
        $permissions = $this->roleService->getAllPermissions();

        // Optionally group them for the frontend
        $grouped = $permissions->groupBy('group');

        return $this->successResponse($grouped, 'Permissions retrieved successfully');
    }
}
