<?php

namespace App\Http\Controllers;

use App\Exceptions\CopilotException;
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

        try {
            $result = $this->copilotService->analyze($validated['prompt'], $request->user());
        } catch (CopilotException $exception) {
            return response()->json(
                ['error' => $exception->getMessage(), 'details' => $exception->details()],
                $exception->status(),
            );
        }

        return response()->json([
            'data' => $result['data']->toArray(),
        ]);
    }
}
