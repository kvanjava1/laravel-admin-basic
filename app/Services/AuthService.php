<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class AuthService
{
    /**
     * Attempt to authenticate a user and issue a token.
     *
     * @param array $credentials
     * @return array
     * @throws ValidationException
     */
    public function attemptLogin(array $credentials)
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // 1. Check if user is Banned
        if ($user->status && $user->status->name === 'Banned') {
            $latestBan = $user->banHistories()
                ->where('action', 'banned')
                ->latest()
                ->first();

            $reason = $latestBan ? ". Reason: {$latestBan->reason}" : "";
            $expiry = ($latestBan && $latestBan->type === 'temporary' && $latestBan->expired_at)
                ? " until {$latestBan->expired_at->format('M d, Y H:i')}"
                : "";

            throw ValidationException::withMessages([
                'email' => ["Your account has been banned{$expiry}{$reason}"],
            ]);
        }

        // 2. Check if user is Inactive
        if ($user->status && $user->status->name === 'Inactive') {
            throw ValidationException::withMessages([
                'email' => ['Your account is currently inactive. Please contact the administrator.'],
            ]);
        }

        $token = $user->createToken('admin-dashboard')->plainTextToken;

        return [
            'user' => $user->load('status'),
            'token' => $token,
        ];
    }

    /**
     * Revoke the user's current access token.
     *
     * @param Request $request
     * @return void
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
    }
}
