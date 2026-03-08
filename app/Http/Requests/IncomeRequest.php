<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IncomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
            ],
            'description' => [
                'nullable',
            ],
            'amount' => [
                'required',
                'numeric',
            ],
            'date' => [
                'required',
                'date',
            ],
            'category_id' => [
                'required',
                'integer',
                'exists:categories,id',
            ],
        ];
    }
}
