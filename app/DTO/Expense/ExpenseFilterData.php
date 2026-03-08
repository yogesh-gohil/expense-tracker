<?php

namespace App\DTO\Expense;

use Illuminate\Http\Request;

class ExpenseFilterData
{
    public function __construct(
        public readonly int $userId,
        public readonly int|string $limit = 15,
        public readonly ?int $categoryId = null,
        public readonly ?string $search = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $limit = $request->query('limit', 15);

        return new self(
            userId: (int) $request->user()->id,
            limit: $limit === 'all' ? 'all' : (int) $limit,
            categoryId: $request->filled('category_id') ? (int) $request->query('category_id') : null,
            search: $request->filled('search') ? (string) $request->query('search') : null,
        );
    }
}
