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

    public function logout(Request $request): void
    {
        $request->user()->tokens()->delete();
    }
}
