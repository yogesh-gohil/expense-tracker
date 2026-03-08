<?php

namespace App\DTO\Category;

use Illuminate\Http\Request;

class CategoryFilterData
{
    public function __construct(
        public readonly int $userId,
        public readonly int|string $limit = 5,
        public readonly ?string $type = null,
        public readonly ?string $search = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $limit = $request->query('limit', 5);

        return new self(
            userId: (int) $request->user()->id,
            limit: $limit === 'all' ? 'all' : (int) $limit,
            type: $request->filled('type') ? (string) $request->query('type') : null,
            search: $request->filled('search') ? (string) $request->query('search') : null,
        );
    }
}
