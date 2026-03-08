<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ExpenseCopilotController extends Controller
{
    public function copilot(Request $request)
    {
        $validated = $request->validate([
            'prompt' => 'required|string',
        ]);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.env('OPENROUTER_API_KEY'),
            'HTTP-Referer' => url('/'),
            'X-Title' => 'Expense Tracker AI',
        ])->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => 'deepseek/deepseek-r1:free',
            'messages' => [
                ['role' => 'system', 'content' => 'Extract transaction details in JSON format. Decide if it is an income or expense.'],
                ['role' => 'user', 'content' => $validated['prompt']],
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
                            'source' => ['type' => 'string'], // for income
                            'vendor' => ['type' => 'string'], // for expense
                            'category' => ['type' => 'string'],
                            'date' => ['type' => 'string'],
                            'notes' => ['type' => 'string'],
                        ],
                        'required' => ['type', 'amount', 'currency', 'category', 'date'],
                    ],
                ],
            ],
        ]);

        if (! $response->successful()) {
            return response()->json(['error' => 'AI request failed'], 500);
        }

        $data = json_decode($response->json()['choices'][0]['message']['content'], true);

        // Save based on type
        if ($data['type'] === 'income') {
            $income = Income::create([
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'source' => $data['source'] ?? $data['vendor'] ?? 'Unknown',
                'category' => $data['category'],
                'date' => $data['date'],
                'notes' => $data['notes'] ?? null,
            ]);

            return response()->json([
                'message' => 'Income created successfully!',
                'income' => $income,
            ]);
        } else {
            $expense = Expense::create([
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'vendor' => $data['vendor'] ?? $data['source'] ?? 'Unknown',
                'category' => $data['category'],
                'date' => $data['date'],
                'notes' => $data['notes'] ?? null,
            ]);

            return response()->json([
                'message' => 'Expense created successfully!',
                'expense' => $expense,
            ]);
        }
    }
}
