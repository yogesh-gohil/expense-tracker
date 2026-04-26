<?php

namespace App\DTO\Copilot;

readonly class TransactionExtractionData
{
    public function __construct(
        public string $type,
        public int|float|null $amount,
        public ?string $currency,
        public ?string $category,
        public string $date,
        public ?string $title,
        public ?string $description,
        public ?string $source,
        public ?string $vendor,
        public ?string $notes,
    ) {}

    public static function fromArray(array $attributes): self
    {
        return new self(
            type: self::stringOrDefault($attributes, 'type', 'expense'),
            amount: self::numericOrNull($attributes['amount'] ?? null),
            currency: self::nullableString($attributes['currency'] ?? null),
            category: self::nullableString($attributes['category'] ?? null),
            date: self::stringOrDefault($attributes, 'date', now()->toDateString()),
            title: self::nullableString($attributes['title'] ?? null),
            description: self::nullableString($attributes['description'] ?? null),
            source: self::nullableString($attributes['source'] ?? null),
            vendor: self::nullableString($attributes['vendor'] ?? null),
            notes: self::nullableString($attributes['notes'] ?? null),
        );
    }

    private static function stringOrDefault(array $attributes, string $key, string $default): string
    {
        $value = self::nullableString($attributes[$key] ?? null);

        return $value ?? $default;
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private static function numericOrNull(mixed $value): int|float|null
    {
        if (! is_numeric($value)) {
            return null;
        }

        return $value + 0;
    }
}
