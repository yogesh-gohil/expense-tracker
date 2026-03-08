<?php

namespace App\DTO\Profile;

use App\Http\Requests\ProfilePasswordUpdateRequest;

class PasswordData
{
    public function __construct(
        public readonly int $userId,
        public readonly string $currentPassword,
        public readonly string $password,
    ) {}

    public static function fromRequest(ProfilePasswordUpdateRequest $request): self
    {
        $data = $request->validated();

        return new self(
            userId: (int) $request->user()->id,
            currentPassword: (string) $data['current_password'],
            password: (string) $data['password'],
        );
    }
}
