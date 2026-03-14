<?php

namespace App\Services;

use App\DTO\Copilot\CopilotResultData;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ExpenseCopilotService
{
    public function analyze(string $prompt, User $user): array
    {
        $apiKey = env('OPENROUTER_API_KEY');
        $apiBaseUrl = env('OPENROUTER_API_BASE_URL', 'https://openrouter.ai/api/v1');
        $model = env('OPENROUTER_MODEL', 'openrouter/free');
        
        if (! $apiKey) {
            return [
                'error' => 'OPENROUTER_API_KEY is not configured.',
                'status' => 422,
            ];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$apiKey,
            'HTTP-Referer' => url('/'),
            'X-Title' => 'Expense Tracker AI',
        ])->post($apiBaseUrl.'/chat/completions', [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $this->systemPrompt()],
                ['role' => 'user', 'content' => $prompt],
            ],
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'transaction_schema',
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'type' => ['type' => 'string', 'enum' => ['income', 'expense']],
                            'amount' => ['type' => 'number'],
                            'currency' => ['type' => 'string'],
                            'category' => ['type' => 'string'],
                            'date' => ['type' => 'string'],
                            'title' => ['type' => 'string'],
                            'description' => ['type' => 'string'],
                            'source' => ['type' => 'string'],
                            'vendor' => ['type' => 'string'],
                            'notes' => ['type' => 'string'],
                        ],
                        'required' => ['type', 'amount', 'category', 'date'],
                    ],
                ],
            ],
        ]);

        if (! $response->successful()) {
            $payload = [
                'error' => 'AI request failed',
                'status' => $response->status(),
            ];
            if (config('app.debug')) {
                $payload['details'] = $response->json() ?? $response->body();
            }

            return $payload;
        }

        $content = $response->json('choices.0.message.content');
        $data = json_decode($content, true);

        if (! is_array($data)) {
            return [
                'error' => 'AI response was not valid JSON',
                'status' => 422,
            ];
        }

        $type = $data['type'] ?? 'expense';
        $categoryType = strtolower((string) $type) === 'income'
            ? Category::TYPE_INCOME
            : Category::TYPE_EXPENSE;
        $categoryName = isset($data['category']) ? trim((string) $data['category']) : null;
        $categoryMatch = $categoryName
            ? $this->findCategoryMatch((int) $user->id, $categoryType, $categoryName)
            : null;

        $userCurrency = strtoupper((string) ($user->currency ?? 'USD'));
        $currency = $data['currency'] ?? $userCurrency;

        $result = new CopilotResultData(
            type: $type,
            amount: $data['amount'] ?? null,
            currency: $currency ? strtoupper((string) $currency) : $userCurrency,
            category: $categoryName ? Str::title($categoryName) : null,
            categoryType: $categoryType,
            categoryMatch: $categoryMatch,
            date: $data['date'] ?? now()->toDateString(),
            title: $data['title'] ?? null,
            description: $data['description'] ?? $data['notes'] ?? null,
            source: $data['source'] ?? null,
            vendor: $data['vendor'] ?? null,
            rawPrompt: $prompt,
        );

        return [
            'data' => $result,
        ];
    }

    private function systemPrompt(): string
    {
        return implode("\n", [
            'You extract a single transaction from the user message and respond ONLY with JSON that matches the provided schema.',
            'Classify type as "expense" or "income".',
            'Amount: return a number (no currency symbols). If multiple amounts appear, pick the most likely transaction amount.',
            'Currency: use the currency code if explicitly mentioned (USD, INR, EUR, GBP, CAD, AUD). If not mentioned, leave empty.',
            'Category: choose a short, human-friendly category (e.g., Food, Transport, Rent, Shopping, Salary, Freelance, Bills, Entertainment, Travel, Health, Education).',
            'Title: create a concise title (3-6 words) describing the transaction. Prefer vendor/source + category.',
            'Description: optional, short notes if helpful.',
            'Date rules (IMPORTANT):',
            '- If the user explicitly mentions a date, convert it to ISO YYYY-MM-DD.',
            '- If the user uses relative dates like today, yesterday, tomorrow, last Friday, next Monday, etc., resolve them based on TODAY = '.$this->todayDate().'.',
            '- If no date is mentioned, set date to TODAY = '.$this->todayDate().'.',
            'Never invent a date that is not grounded in the message or TODAY.',
        ]);
    }

    private function todayDate(): string
    {
        return now()->toDateString();
    }

    private function findCategoryMatch(int $userId, string $type, string $name): ?array
    {
        $normalizedNeedle = $this->normalizeCategory($name);

        if ($normalizedNeedle === '') {
            return null;
        }

        $categories = Category::query()
            ->where('user_id', $userId)
            ->where('type', $type)
            ->get(['id', 'name']);

        foreach ($categories as $category) {
            $normalizedCategory = $this->normalizeCategory((string) $category->name);
            if ($normalizedCategory === $normalizedNeedle) {
                return [
                    'exists' => true,
                    'id' => (int) $category->id,
                    'name' => (string) $category->name,
                ];
            }
        }

        foreach ($categories as $category) {
            $normalizedCategory = $this->normalizeCategory((string) $category->name);
            if ($normalizedCategory !== '' && (str_contains($normalizedCategory, $normalizedNeedle) || str_contains($normalizedNeedle, $normalizedCategory))) {
                return [
                    'exists' => true,
                    'id' => (int) $category->id,
                    'name' => (string) $category->name,
                ];
            }
        }

        return [
            'exists' => false,
            'id' => null,
            'name' => null,
        ];
    }

    private function normalizeCategory(string $value): string
    {
        $value = strtolower($value);
        $value = str_replace('&', 'and', $value);
        $value = preg_replace('/[^a-z0-9]+/', '', $value) ?? '';
        $value = preg_replace('/(ing|s)$/', '', $value) ?? $value;

        return $value;
    }
}
