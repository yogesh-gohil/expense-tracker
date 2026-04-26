<?php

namespace App\Ai\Agents;

use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

class TransactionExtractorAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    /**
     * @param  array<int, string>  $categoryNames
     */
    public function __construct(
        private readonly User $user,
        private readonly array $categoryNames = [],
    ) {}

    public function instructions(): Stringable|string
    {
        $lines = [
            'You extract a single financial transaction from the user message.',
            'Return structured data only.',
            'Classify the transaction type as "expense" or "income". Default to "expense" when unclear.',
            'Return amount as a number without currency symbols or separators.',
            'Only include a currency code when the user clearly mentions one.',
            'Choose a short, human-friendly category.',
            'Create a concise title of 3 to 6 words when possible.',
            'Description, source, vendor, and notes are optional and should stay brief.',
            'Date rules:',
            '- Convert explicit dates to ISO format YYYY-MM-DD.',
            '- Resolve relative dates using TODAY = '.$this->todayDate().'.',
            '- If no date is mentioned, use TODAY = '.$this->todayDate().'.',
            'Never invent multiple transactions. Pick the most likely single transaction.',
        ];

        if ($this->categoryNames !== []) {
            $lines[] = 'Prefer one of these existing categories when it fits naturally: '.implode(', ', $this->categoryNames).'.';
        }

        $lines[] = 'The user default currency is '.$this->userCurrency().'.';

        return implode("\n", $lines);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'type' => $schema->string()->enum(['income', 'expense'])->required(),
            'amount' => $schema->number()->nullable()->required(),
            'currency' => $schema->string()->nullable(),
            'category' => $schema->string()->nullable()->required(),
            'date' => $schema->string()->required(),
            'title' => $schema->string()->nullable(),
            'description' => $schema->string()->nullable(),
            'source' => $schema->string()->nullable(),
            'vendor' => $schema->string()->nullable(),
            'notes' => $schema->string()->nullable(),
        ];
    }

    public function provider(): Lab|string|array|null
    {
        return config('ai.copilot.provider', Lab::OpenRouter->value);
    }

    public function model(): ?string
    {
        return config('ai.copilot.model');
    }

    public function timeout(): int
    {
        return (int) config('ai.copilot.timeout', 60);
    }

    private function todayDate(): string
    {
        return now()->toDateString();
    }

    private function userCurrency(): string
    {
        return strtoupper((string) ($this->user->currency ?? 'USD'));
    }
}
