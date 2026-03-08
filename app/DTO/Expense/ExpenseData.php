<?php

namespace App\DTO\Expense;

use App\Http\Requests\ExpenseRequest;

class ExpenseData
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $description,
        public readonly int|float|string $amount,
        public readonly string $date,
        public readonly int $categoryId,
        public readonly int $userId,
    ) {}

    public static function fromRequest(ExpenseRequest $request): self
    {
        $data = $request->validated();

        return new self(
            title: $data['title'],
            description: $data['description'] ?? null,
            amount: $data['amount'],
            date: $data['date'],
            categoryId: (int) $data['category_id'],
            userId: (int) $request->user()->id,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'amount' => $this->amount,
            'date' => $this->date,
            'category_id' => $this->categoryId,
            'user_id' => $this->userId,
        ];
    }
}
