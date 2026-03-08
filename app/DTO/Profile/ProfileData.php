<?php

namespace App\DTO\Profile;

use App\Http\Requests\ProfileUpdateRequest;

class ProfileData
{
    public function __construct(
        public readonly int $userId,
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $phone,
        public readonly ?string $currency,
        public readonly ?string $bio,
    ) {}

    public static function fromRequest(ProfileUpdateRequest $request): self
    {
        $data = $request->validated();

        return new self(
            userId: (int) $request->user()->id,
            name: (string) $data['name'],
            email: (string) $data['email'],
            phone: $data['phone'] ?? null,
            currency: isset($data['currency']) ? strtoupper((string) $data['currency']) : null,
            bio: $data['bio'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'currency' => $this->currency,
            'bio' => $this->bio,
        ];
    }
}
