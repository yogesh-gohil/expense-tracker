<?php

namespace Tests\Feature;

use App\Ai\Agents\TransactionExtractorAgent;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ExpenseCopilotControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_analyze_a_prompt(): void
    {
        Carbon::setTestNow('2026-04-25 09:00:00');

        config([
            'ai.copilot.provider' => 'openrouter',
            'ai.providers.openrouter.key' => 'test-key',
        ]);

        $user = User::factory()->create(['currency' => 'INR']);

        Category::query()->create([
            'name' => 'Food',
            'description' => null,
            'type' => Category::TYPE_EXPENSE,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        TransactionExtractorAgent::fake([
            [
                'type' => 'expense',
                'amount' => 50,
                'currency' => null,
                'category' => 'food',
                'date' => '2026-04-25',
                'title' => 'Dinner at Domino\'s',
                'description' => 'Pizza dinner',
                'source' => null,
                'vendor' => 'Domino\'s',
                'notes' => null,
            ],
        ])->preventStrayPrompts();

        $response = $this->postJson('/api/copilot', [
            'prompt' => 'Ate pizza at Domino\'s for 50 today',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.type', 'expense')
            ->assertJsonPath('data.amount', 50)
            ->assertJsonPath('data.currency', 'INR')
            ->assertJsonPath('data.category', 'Food')
            ->assertJsonPath('data.category_type', Category::TYPE_EXPENSE)
            ->assertJsonPath('data.category_match.exists', true)
            ->assertJsonPath('data.category_match.name', 'Food')
            ->assertJsonPath('data.date', '2026-04-25')
            ->assertJsonPath('data.title', 'Dinner at Domino\'s')
            ->assertJsonPath('data.description', 'Pizza dinner')
            ->assertJsonPath('data.vendor', 'Domino\'s')
            ->assertJsonPath('data.raw_prompt', 'Ate pizza at Domino\'s for 50 today');

        $this->assertSame([
            'type',
            'amount',
            'currency',
            'category',
            'category_type',
            'category_match',
            'date',
            'title',
            'description',
            'source',
            'vendor',
            'raw_prompt',
        ], array_keys($response->json('data')));

        TransactionExtractorAgent::assertPrompted('Ate pizza at Domino\'s for 50 today');
    }

    public function test_prompt_is_required(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/copilot', [
            'prompt' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['prompt']);

        TransactionExtractorAgent::assertNeverPrompted();
    }

    public function test_provider_failure_returns_stable_error_response(): void
    {
        config([
            'ai.copilot.provider' => 'openrouter',
            'ai.providers.openrouter.key' => 'test-key',
        ]);

        Sanctum::actingAs(User::factory()->create());

        TransactionExtractorAgent::fake([
            fn () => throw new \RuntimeException('Provider unavailable'),
        ])->preventStrayPrompts();

        $response = $this->postJson('/api/copilot', [
            'prompt' => 'Salary from ACME yesterday 5000',
        ]);

        $response->assertStatus(502)
            ->assertJsonPath('error', 'AI request failed')
            ->assertJsonPath('details', 'Provider unavailable');
    }
}
