<?php

namespace App\Http\Controllers;

use App\Services\ExpenseCopilotService;
use Illuminate\Http\Request;

class ExpenseCopilotController extends Controller
{
    public function __construct(
        private readonly ExpenseCopilotService $copilotService,
    ) {}

    public function copilot(Request $request)
    {
        $validated = $request->validate([
            'prompt' => 'required|string',
        ]);

        $result = $this->copilotService->analyze($validated['prompt'], $request->user());

        if (isset($result['error'])) {
            return response()->json(
                ['error' => $result['error'], 'details' => $result['details'] ?? null],
                $result['status'] ?? 500,
            );
        }

        return response()->json([
            'data' => $result['data']->toArray(),
        ]);
    }
}
