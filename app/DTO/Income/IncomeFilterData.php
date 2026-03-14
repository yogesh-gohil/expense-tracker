<?php

namespace App\DTO\Income;

use Illuminate\Http\Request;

class IncomeFilterData
{
    public function __construct(
        public readonly int $userId,
        public readonly int|string $limit = 15,
        public readonly ?int $categoryId = null,
        public readonly ?int $month = null,
        public readonly ?int $year = null,
        public readonly ?string $sortBy = null,
        public readonly ?string $sortDir = null,
        public readonly ?string $search = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $limit = $request->query('limit', 15);

        return new self(
            userId: (int) $request->user()->id,
            limit: $limit === 'all' ? 'all' : (int) $limit,
            categoryId: $request->filled('category_id') ? (int) $request->query('category_id') : null,
            month: $request->filled('month') ? (int) $request->query('month') : null,
            year: $request->filled('year') ? (int) $request->query('year') : null,
            sortBy: $request->filled('sort_by') ? (string) $request->query('sort_by') : null,
            sortDir: $request->filled('sort_dir') ? (string) $request->query('sort_dir') : null,
            search: $request->filled('search') ? (string) $request->query('search') : null,
        );
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'limit' => $this->limit,
            'category_id' => $this->categoryId,
            'month' => $this->month,
            'year' => $this->year,
            'sort_by' => $this->sortBy,
            'sort_dir' => $this->sortDir,
            'search' => $this->search,
        ];
    }
}
