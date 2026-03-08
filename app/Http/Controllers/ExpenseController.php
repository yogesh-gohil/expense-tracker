<?php

namespace App\Http\Controllers;

use App\DTO\Expense\ExpenseData;
use App\DTO\Expense\ExpenseFilterData;
use App\DTO\Expense\ExpenseSummaryFilterData;
use App\Http\Requests\ExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Services\ExpenseService;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function __construct(
        private readonly ExpenseService $expenseService,
    ) {}

    public function index(Request $request)
    {
        $expenses = $this->expenseService->list(ExpenseFilterData::fromRequest($request));

        return ExpenseResource::collection($expenses);
    }

    public function store(ExpenseRequest $request)
    {
        $expense = $this->expenseService->create(ExpenseData::fromRequest($request));

        return new ExpenseResource($expense);
    }

    public function monthlySummary(Request $request)
    {
        $summary = $this->expenseService->monthlySummary(ExpenseSummaryFilterData::fromRequest($request));

        return response()->json([
            'data' => $summary,
        ]);
    }

    public function show(Request $request, Expense $expense)
    {
        $expense = $this->expenseService->get($expense, (int) $request->user()->id);

        return new ExpenseResource($expense);
    }

    public function update(ExpenseRequest $request, Expense $expense)
    {
        $expense = $this->expenseService->update($expense, ExpenseData::fromRequest($request));

        return new ExpenseResource($expense);
    }

    public function destroy(Request $request, Expense $expense)
    {
        $this->expenseService->delete($expense, (int) $request->user()->id);

        return response()->noContent();
    }
}
