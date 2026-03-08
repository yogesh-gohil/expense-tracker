<?php

namespace App\DTO\Category;

use App\Http\Requests\CategoryRequest;

class CategoryData
{
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly ?string $description,
        public readonly int $userId,
    ) {}

    public static function fromRequest(CategoryRequest $request): self
    {
        $data = $request->validated();

        return new self(
            name: $data['name'],
            type: $data['type'],
            description: $data['description'] ?? null,
            userId: (int) $request->user()->id,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'user_id' => $this->userId,
        ];
    }
}
