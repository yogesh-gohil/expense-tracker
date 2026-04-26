<?php

namespace App\Services;

use App\Ai\Agents\TransactionExtractorAgent;
use App\DTO\Copilot\CopilotResultData;
use App\DTO\Copilot\TransactionExtractionData;
use App\Exceptions\CopilotException;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Str;
use Laravel\Ai\Responses\StructuredAgentResponse;
use Throwable;

class ExpenseCopilotService
{
    public function analyze(string $prompt, User $user): array
    {
        $provider = config('ai.copilot.provider', 'openrouter');
        $providerConfig = config("ai.providers.{$provider}");

        if (! $providerConfig) {
            throw new CopilotException("AI provider [{$provider}] is not configured.", 422);
        }

        $requiresKey = $providerConfig['requires_key'] ?? true;
        $providerKey = $providerConfig['key'] ?? null;

        if ($requiresKey && empty($providerKey)) {
            throw new CopilotException(strtoupper($provider) . ' API key is not configured.', 422);
        }

        $extraction = $this->extractTransaction($prompt, $user);
        $result = $this->mapExtractionToResult($extraction, $prompt, $user);

        return [
            'data' => $result,
        ];
    }

    private function extractTransaction(string $prompt, User $user): TransactionExtractionData
    {
        $transactionAgent = TransactionExtractorAgent::make(
            user: $user,
            categoryNames: $this->categoryNamesForUser($user),
        );

        try {
            $response = $transactionAgent->prompt($prompt);
        } catch (RequestException $exception) {
            throw new CopilotException(
                'AI request failed',
                $exception->response?->status() ?? 502,
                config('app.debug') ? ($exception->response?->json() ?? $exception->response?->body()) : null,
            );
        } catch (Throwable $exception) {
            throw new CopilotException(
                'AI request failed',
                502,
                config('app.debug') ? $exception->getMessage() : null,
            );
        }

        if (! $response instanceof StructuredAgentResponse) {
            throw new CopilotException('AI response was not valid structured data', 422);
        }

        $data = $response->toArray();

        if (! is_array($data)) {
            throw new CopilotException('AI response was not valid structured data', 422);
        }

        return TransactionExtractionData::fromArray($data);
    }

    private function mapExtractionToResult(TransactionExtractionData $extraction, string $prompt, User $user): CopilotResultData
    {
        $type = strtolower($extraction->type) === 'income' ? 'income' : 'expense';
        $categoryType = $type === 'income'
            ? Category::TYPE_INCOME
            : Category::TYPE_EXPENSE;
        $categoryName = $extraction->category;
        $categoryMatch = $categoryName
            ? $this->findCategoryMatch((int) $user->id, $categoryType, $categoryName)
            : null;

        $userCurrency = strtoupper((string) ($user->currency ?? 'USD'));
        $currency = $extraction->currency ? strtoupper($extraction->currency) : $userCurrency;

        return new CopilotResultData(
            type: $type,
            amount: $extraction->amount,
            currency: $currency,
            category: $categoryName ? Str::title($categoryName) : null,
            categoryType: $categoryType,
            categoryMatch: $categoryMatch,
            date: $extraction->date ?: now()->toDateString(),
            title: $extraction->title,
            description: $extraction->description ?? $extraction->notes,
            source: $extraction->source,
            vendor: $extraction->vendor,
            rawPrompt: $prompt,
        );
    }

    /**
     * @return array<int, string>
     */
    private function categoryNamesForUser(User $user): array
    {
        return Category::query()
            ->where('user_id', $user->id)
            ->orderBy('name')
            ->pluck('name')
            ->filter(fn ($name) => is_string($name) && trim($name) !== '')
            ->values()
            ->all();
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
