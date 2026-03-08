<?php

namespace App\Services;

use App\DTO\Profile\PasswordData;
use App\DTO\Profile\ProfileData;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileService
{
    public function get(int $userId): User
    {
        return User::query()->findOrFail($userId);
    }

    public function update(ProfileData $data): User
    {
        $user = $this->get($data->userId);

        return DB::transaction(function () use ($user, $data) {
            $user->update($data->toArray());

            return $user->refresh();
        });
    }

    public function updatePassword(PasswordData $data): void
    {
        $user = $this->get($data->userId);

        if (! Hash::check($data->currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        DB::transaction(function () use ($user, $data) {
            $user->update([
                'password' => $data->password,
            ]);
        });
    }
}
