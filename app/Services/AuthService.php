<?php

namespace App\Services;

use App\DTO\Auth\LoginData;
use App\DTO\Auth\RegisterData;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(RegisterData $data): User
    {
        return DB::transaction(function () use ($data) {
            return User::query()->create($data->toArray());
        });
    }

    public function login(LoginData $data, Request $request): User
    {
        if (! Auth::attempt($data->toCredentials())) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $request->session()->regenerate();

        return Auth::user();
    }

    public function loginWithToken(LoginData $data, Request $request): array
    {
        if (! Auth::attempt($data->toCredentials())) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();
        $deviceName = (string) ($request->input('device_name') ?: $request->userAgent() ?: 'mobile');
        $token = $user->createToken($deviceName)->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logout(Request $request): void
    {
        $token = $request->user()?->currentAccessToken();

        if ($token) {
            $token->delete();
            return;
        }

        $request->user()?->tokens()->delete();
    }
}
