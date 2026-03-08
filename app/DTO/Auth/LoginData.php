<?php

namespace App\DTO\Auth;

use App\Http\Requests\LoginRequest;

class LoginData
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {}

    public static function fromRequest(LoginRequest $request): self
    {
        $data = $request->validated();

        return new self(
            email: $data['email'],
            password: $data['password'],
        );
    }

    public function toCredentials(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}
