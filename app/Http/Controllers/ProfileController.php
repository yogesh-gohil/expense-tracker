<?php

namespace App\Http\Controllers;

use App\DTO\Profile\PasswordData;
use App\DTO\Profile\ProfileData;
use App\Http\Requests\ProfilePasswordUpdateRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Resources\UserResource;
use App\Services\ProfileService;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profileService,
    ) {}

    public function show(Request $request)
    {
        $user = $this->profileService->get((int) $request->user()->id);

        return new UserResource($user);
    }

    public function update(ProfileUpdateRequest $request)
    {
        $user = $this->profileService->update(ProfileData::fromRequest($request));

        return new UserResource($user);
    }

    public function updatePassword(ProfilePasswordUpdateRequest $request)
    {
        $this->profileService->updatePassword(PasswordData::fromRequest($request));

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }
}
