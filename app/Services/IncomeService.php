<?php

namespace App\Services;

use App\DTO\Income\IncomeData;
use App\DTO\Income\IncomeFilterData;
use App\DTO\Income\IncomeSummaryFilterData;
use App\Models\Category;
use App\Models\Income;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class IncomeService
{
    public function list(IncomeFilterData $filter): Collection|LengthAwarePaginator
    {
        $query = Income::query()
            ->with('category')
            ->where('user_id', $filter->userId)
            ->latest();

        if ($filter->categoryId !== null) {
            $query->where('category_id', $filter->categoryId);
        }

        if ($filter->search !== null) {
            $query->where('title', 'LIKE', '%'.$filter->search.'%');
        }

        if ($filter->limit === 'all') {
            return $query->get();
        }

        return $query->paginate($filter->limit);
    }

    public function create(IncomeData $data): Income
    {
        return DB::transaction(function () use ($data) {
            return Income::query()->create($data->toArray())->load('category');
        });
    }

    public function monthlySummary(IncomeSummaryFilterData $filter): array
    {
        $totalIncome = (int) Income::query()
            ->join('categories', 'categories.id', '=', 'incomes.category_id')
            ->where('incomes.user_id', $filter->userId)
            ->where('categories.type', Category::TYPE_INCOME)
            ->whereYear('incomes.date', $filter->year)
            ->whereMonth('incomes.date', $filter->month)
            ->sum('incomes.amount');

        $categorySummary = Income::query()
            ->selectRaw('categories.id as category_id, categories.name as category_name, SUM(incomes.amount) as total_income')
            ->join('categories', 'categories.id', '=', 'incomes.category_id')
            ->where('incomes.user_id', $filter->userId)
            ->where('categories.type', Category::TYPE_INCOME)
            ->whereYear('incomes.date', $filter->year)
            ->whereMonth('incomes.date', $filter->month)
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_income')
            ->get();

        $transactionsCount = (int) Income::query()
            ->join('categories', 'categories.id', '=', 'incomes.category_id')
            ->where('incomes.user_id', $filter->userId)
            ->where('categories.type', Category::TYPE_INCOME)
            ->whereYear('incomes.date', $filter->year)
            ->whereMonth('incomes.date', $filter->month)
            ->count();

        $currentMonth = CarbonImmutable::create($filter->year, $filter->month, 1);
        $previousMonth = $currentMonth->subMonth();
        $previousTotalIncome = (int) Income::query()
            ->join('categories', 'categories.id', '=', 'incomes.category_id')
            ->where('incomes.user_id', $filter->userId)
            ->where('categories.type', Category::TYPE_INCOME)
            ->whereYear('incomes.date', $previousMonth->year)
            ->whereMonth('incomes.date', $previousMonth->month)
            ->sum('incomes.amount');

        $changeAmount = $totalIncome - $previousTotalIncome;

        return [
            'month' => $filter->month,
            'year' => $filter->year,
            'total_income' => $totalIncome,
            'transactions_count' => $transactionsCount,
            'average_transaction' => $transactionsCount > 0 ? (int) round($totalIncome / $transactionsCount) : 0,
            'top_category' => $categorySummary->isNotEmpty()
                ? [
                    'category_id' => (int) $categorySummary[0]->category_id,
                    'category_name' => (string) $categorySummary[0]->category_name,
                    'total_income' => (int) $categorySummary[0]->total_income,
                ]
                : null,
            'trend' => [
                'previous_month' => $previousMonth->month,
                'previous_year' => $previousMonth->year,
                'previous_total_income' => $previousTotalIncome,
                'change_amount' => $changeAmount,
                'change_percentage' => $previousTotalIncome > 0
                    ? round(($changeAmount / $previousTotalIncome) * 100, 2)
                    : null,
            ],
            'categories' => $categorySummary
                ->map(function ($item) use ($totalIncome) {
                    $categoryTotal = (int) $item->total_income;

                    return [
                        'category_id' => (int) $item->category_id,
                        'category_name' => (string) $item->category_name,
                        'total_income' => $categoryTotal,
                        'percentage' => $totalIncome > 0
                            ? round(($categoryTotal / $totalIncome) * 100, 2)
                            : 0,
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    public function get(Income $income, int $userId): Income
    {
        $this->ensureOwnedByUser($income, $userId);

        return $income->load('category');
    }

    public function update(Income $income, IncomeData $data): Income
    {
        $this->ensureOwnedByUser($income, $data->userId);

        return DB::transaction(function () use ($income, $data) {
            $income->update($data->toArray());

            return $income->refresh()->load('category');
        });
    }

    public function delete(Income $income, int $userId): void
    {
        $this->ensureOwnedByUser($income, $userId);

        DB::transaction(function () use ($income) {
            $income->delete();
        });
    }

    private function ensureOwnedByUser(Income $income, int $userId): void
    {
        if ((int) $income->user_id !== $userId) {
            throw new AuthorizationException('You are not allowed to access this income.');
        }
    }
}
