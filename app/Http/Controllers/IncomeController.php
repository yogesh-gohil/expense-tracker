<?php

namespace App\Http\Controllers;

use App\DTO\Income\IncomeData;
use App\DTO\Income\IncomeFilterData;
use App\DTO\Income\IncomeSummaryFilterData;
use App\Http\Requests\IncomeRequest;
use App\Http\Resources\IncomeResource;
use App\Models\Income;
use App\Services\IncomeService;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    public function __construct(
        private readonly IncomeService $incomeService,
    ) {}

    public function index(Request $request)
    {
        $incomes = $this->incomeService->list(IncomeFilterData::fromRequest($request));

        return IncomeResource::collection($incomes);
    }

    public function store(IncomeRequest $request)
    {
        $income = $this->incomeService->create(IncomeData::fromRequest($request));

        return new IncomeResource($income);
    }

    public function monthlySummary(Request $request)
    {
        $summary = $this->incomeService->monthlySummary(IncomeSummaryFilterData::fromRequest($request));

        return response()->json([
            'data' => $summary,
        ]);
    }

    public function show(Request $request, Income $income)
    {
        $income = $this->incomeService->get($income, (int) $request->user()->id);

        return new IncomeResource($income);
    }

    public function update(IncomeRequest $request, Income $income)
    {
        $income = $this->incomeService->update($income, IncomeData::fromRequest($request));

        return new IncomeResource($income);
    }

    public function destroy(Request $request, Income $income)
    {
        $this->incomeService->delete($income, (int) $request->user()->id);

        return response()->noContent();
    }
}
