<?php

namespace Tests\Unit;

use App\Ai\Agents\TransactionExtractorAgent;
use App\Exceptions\CopilotException;
use App\Models\Category;
use App\Models\User;
use App\Services\ExpenseCopilotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ExpenseCopilotServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_maps_expense_results_with_exact_category_match(): void
    {
        Carbon::setTestNow('2026-04-25 09:00:00');

        [$service, $user] = $this->makeServiceAndUser(currency: 'usd');

        $category = Category::query()->create([
            'name' => 'Food',
            'description' => null,
            'type' => Category::TYPE_EXPENSE,
            'user_id' => $user->id,
        ]);

        TransactionExtractorAgent::fake([
            [
                'type' => 'expense',
                'amount' => 50.75,
                'currency' => 'usd',
                'category' => 'food',
                'date' => '2026-04-24',
                'title' => 'Dinner',
                'description' => 'Pizza',
                'source' => null,
                'vendor' => 'Domino\'s',
                'notes' => null,
            ],
        ])->preventStrayPrompts();

        $result = $service->analyze('Domino\'s pizza 50.75 yesterday', $user);
        $data = $result['data']->toArray();

        $this->assertSame('expense', $data['type']);
        $this->assertSame(50.75, $data['amount']);
        $this->assertSame('USD', $data['currency']);
        $this->assertSame('Food', $data['category']);
        $this->assertSame(Category::TYPE_EXPENSE, $data['category_type']);
        $this->assertSame([
            'exists' => true,
            'id' => $category->id,
            'name' => 'Food',
        ], $data['category_match']);
        $this->assertSame('2026-04-24', $data['date']);
        $this->assertSame('Dinner', $data['title']);
        $this->assertSame('Pizza', $data['description']);
        $this->assertSame('Domino\'s', $data['vendor']);
    }

    public function test_it_maps_income_results_and_uses_user_currency_fallback(): void
    {
        Carbon::setTestNow('2026-04-25 09:00:00');

        [$service, $user] = $this->makeServiceAndUser(currency: 'inr');

        $category = Category::query()->create([
            'name' => 'Salary',
            'description' => null,
            'type' => Category::TYPE_INCOME,
            'user_id' => $user->id,
        ]);

        TransactionExtractorAgent::fake([
            [
                'type' => 'income',
                'amount' => 5000,
                'currency' => null,
                'category' => 'salary',
                'date' => '2026-04-25',
                'title' => 'Monthly salary',
                'description' => null,
                'source' => 'ACME Corp',
                'vendor' => null,
                'notes' => null,
            ],
        ])->preventStrayPrompts();

        $data = $service->analyze('Received salary from ACME Corp', $user)['data']->toArray();

        $this->assertSame('income', $data['type']);
        $this->assertSame('INR', $data['currency']);
        $this->assertSame(Category::TYPE_INCOME, $data['category_type']);
        $this->assertSame($category->id, $data['category_match']['id']);
        $this->assertSame('ACME Corp', $data['source']);
    }

    public function test_it_falls_back_to_today_and_marks_new_category_when_no_match_exists(): void
    {
        Carbon::setTestNow('2026-04-25 09:00:00');

        [$service, $user] = $this->makeServiceAndUser();

        TransactionExtractorAgent::fake([
            [
                'type' => 'expense',
                'amount' => 12,
                'currency' => null,
                'category' => 'snacks',
                'date' => '',
                'title' => null,
                'description' => null,
                'source' => null,
                'vendor' => null,
                'notes' => 'Late night chips',
            ],
        ])->preventStrayPrompts();

        $data = $service->analyze('chips from the store', $user)['data']->toArray();

        $this->assertSame('2026-04-25', $data['date']);
        $this->assertSame('USD', $data['currency']);
        $this->assertSame('Snacks', $data['category']);
        $this->assertSame([
            'exists' => false,
            'id' => null,
            'name' => null,
        ], $data['category_match']);
        $this->assertSame('Late night chips', $data['description']);
    }

    public function test_it_uses_loose_category_matching(): void
    {
        Carbon::setTestNow('2026-04-25 09:00:00');

        [$service, $user] = $this->makeServiceAndUser();

        $category = Category::query()->create([
            'name' => 'Transport',
            'description' => null,
            'type' => Category::TYPE_EXPENSE,
            'user_id' => $user->id,
        ]);

        TransactionExtractorAgent::fake([
            [
                'type' => 'expense',
                'amount' => 20,
                'currency' => null,
                'category' => 'transportation',
                'date' => '2026-04-25',
                'title' => null,
                'description' => null,
                'source' => null,
                'vendor' => 'Uber',
                'notes' => null,
            ],
        ])->preventStrayPrompts();

        $data = $service->analyze('Uber ride 20', $user)['data']->toArray();

        $this->assertSame([
            'exists' => true,
            'id' => $category->id,
            'name' => 'Transport',
        ], $data['category_match']);
    }

    public function test_it_throws_when_provider_key_is_missing(): void
    {
        $service = new ExpenseCopilotService;
        $user = User::factory()->create();

        config([
            'ai.copilot.provider' => 'openrouter',
            'ai.providers.openrouter.key' => null,
        ]);

        $this->expectException(CopilotException::class);
        $this->expectExceptionMessage('OPENROUTER API key is not configured.');

        $service->analyze('coffee 10', $user);
    }

    /**
     * @return array{0: ExpenseCopilotService, 1: User}
     */
    private function makeServiceAndUser(string $currency = 'usd'): array
    {
        config([
            'ai.copilot.enabled' => true,
            'ai.copilot.provider' => 'openrouter',
            'ai.providers.openrouter.key' => 'test-key',
        ]);

        return [
            new ExpenseCopilotService,
            User::factory()->create(['currency' => $currency]),
        ];
    }
}
