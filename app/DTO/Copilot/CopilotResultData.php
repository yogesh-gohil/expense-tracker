<?php

namespace App\DTO\Copilot;

class CopilotResultData
{
    public function __construct(
        public readonly string $type,
        public readonly int|float|null $amount,
        public readonly string $currency,
        public readonly ?string $category,
        public readonly string $categoryType,
        public readonly ?array $categoryMatch,
        public readonly string $date,
        public readonly ?string $title,
        public readonly ?string $description,
        public readonly ?string $source,
        public readonly ?string $vendor,
        public readonly string $rawPrompt,
    ) {}

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'category' => $this->category,
            'category_type' => $this->categoryType,
            'category_match' => $this->categoryMatch,
            'date' => $this->date,
            'title' => $this->title,
            'description' => $this->description,
            'source' => $this->source,
            'vendor' => $this->vendor,
            'raw_prompt' => $this->rawPrompt,
        ];
    }
}
