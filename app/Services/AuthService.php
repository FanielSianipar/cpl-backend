<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function login(array $credentials)
    {
        if (!Auth::attempt($credentials)) {
            return ['message' => 'Unauthorized'];
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'message'      => 'Login successful',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => $user,
            'roles'        => $user->getRoleNames(),
            'permissions'  => $user->getAllPermissions()->pluck('name'),
        ];
    }

    public function logout($user)
    {
        $user->currentAccessToken()->delete();
    }
}
