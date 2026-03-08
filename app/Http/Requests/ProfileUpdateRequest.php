<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = (int) $this->user()->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email,'.$userId,
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],
            'currency' => [
                'required',
                'string',
                'size:3',
                'in:USD,EUR,GBP,INR,CAD,AUD',
            ],
            'bio' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}
