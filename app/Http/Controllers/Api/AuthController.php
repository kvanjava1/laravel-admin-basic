<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminDashboard\Auth\LoginRequest;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle user login and issue token.
     */
    public function login(LoginRequest $request)
    {
        $data = $this->authService->attemptLogin($request->validated());

        return $this->successResponse($data, 'Login successful');
    }

    /**
     * Handle user logout and revoke token.
     */
    public function logout(Request $request)
    {
        $this->authService->logout($request);

        return $this->successResponse(null, 'Logged out successfully');
    }

    /**
     * Get the authenticated user.
     */
    public function me(Request $request)
    {
        $user = $request->user()->load(['status', 'roles']);
        $userData = $user->toArray();
        $userData['permissions'] = $user->getAllPermissions()->toArray();

        return $this->successResponse($userData, 'User profile retrieved');
    }
}
