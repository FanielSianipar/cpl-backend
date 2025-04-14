<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request)
    {
        // Validasi input login
        $data = $request->validated();
        $result = $this->authService->login($data);
        if (isset($result['message']) && $result['message'] === 'Unauthenticated') {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        return response()->json($result, 200);
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());
        return response()->json(['message' => 'Logged out'], 200);

        // $request->user()->tokens()->delete();

        // return response()->json(['message' => 'Logged out'], 200);
    }
}
