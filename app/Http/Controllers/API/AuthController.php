<?php

namespace App\Http\Controllers\API;

use App\DTO\Auth\LoginData;
use App\DTO\Auth\RegisterData;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    public function register(RegisterRequest $request)
    {
        $user = $this->authService->register(RegisterData::fromRequest($request));

        return new UserResource($user);
    }

    public function login(LoginRequest $request)
    {
        $user = $this->authService->login(LoginData::fromRequest($request), $request);

        return response()->json([
            'user' => $user,
            'message' => 'Login successfully',
        ]);
    }

    public function loginToken(LoginRequest $request)
    {
        $payload = $this->authService->loginWithToken(LoginData::fromRequest($request), $request);

        return response()->json([
            'user' => $payload['user'],
            'token' => $payload['token'],
            'message' => 'Token issued successfully',
        ]);
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request);

        return response()->json([
            'success' => true,
        ]);
    }
}
