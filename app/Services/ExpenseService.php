<?php

namespace App\Services;

use App\DTO\Expense\ExpenseData;
use App\DTO\Expense\ExpenseFilterData;
use App\DTO\Expense\ExpenseSummaryFilterData;
use App\Models\Category;
use App\Models\Expense;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
    public function list(ExpenseFilterData $filter): Collection|LengthAwarePaginator
    {
        $query = Expense::query()
            ->with('category')
            ->applyFilters($filter->toArray())
            ->latest();

        if ($filter->limit === 'all') {
            return $query->get();
        }

        return $query->paginate($filter->limit);
    }

    public function create(ExpenseData $data): Expense
    {
        return DB::transaction(function () use ($data) {
            return Expense::query()->create($data->toArray())->load('category');
        });
    }

    public function monthlySummary(ExpenseSummaryFilterData $filter): array
    {
        $totalSpent = (int) Expense::query()
            ->join('categories', 'categories.id', '=', 'expenses.category_id')
            ->where('expenses.user_id', $filter->userId)
            ->where('categories.type', Category::TYPE_EXPENSE)
            ->whereYear('expenses.date', $filter->year)
            ->whereMonth('expenses.date', $filter->month)
            ->sum('expenses.amount');

        $categorySummary = Expense::query()
            ->selectRaw('categories.id as category_id, categories.name as category_name, SUM(expenses.amount) as total_spent')
            ->join('categories', 'categories.id', '=', 'expenses.category_id')
            ->where('expenses.user_id', $filter->userId)
            ->where('categories.type', Category::TYPE_EXPENSE)
            ->whereYear('expenses.date', $filter->year)
            ->whereMonth('expenses.date', $filter->month)
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_spent')
            ->get();

        $transactionsCount = (int) Expense::query()
            ->join('categories', 'categories.id', '=', 'expenses.category_id')
            ->where('expenses.user_id', $filter->userId)
            ->where('categories.type', Category::TYPE_EXPENSE)
            ->whereYear('expenses.date', $filter->year)
            ->whereMonth('expenses.date', $filter->month)
            ->count();

        $highestExpense = Expense::query()
            ->with('category:id,name')
            ->where('user_id', $filter->userId)
            ->whereHas('category', fn ($query) => $query->where('type', Category::TYPE_EXPENSE))
            ->whereYear('date', $filter->year)
            ->whereMonth('date', $filter->month)
            ->orderByDesc('amount')
            ->first();

        $recentExpenses = Expense::query()
            ->with('category:id,name')
            ->where('user_id', $filter->userId)
            ->whereHas('category', fn ($query) => $query->where('type', Category::TYPE_EXPENSE))
            ->whereYear('date', $filter->year)
            ->whereMonth('date', $filter->month)
            ->latest('date')
            ->latest('id')
            ->limit(5)
            ->get();

        $currentMonth = CarbonImmutable::create($filter->year, $filter->month, 1);
        $previousMonth = $currentMonth->subMonth();
        $previousTotalSpent = (int) Expense::query()
            ->join('categories', 'categories.id', '=', 'expenses.category_id')
            ->where('expenses.user_id', $filter->userId)
            ->where('categories.type', Category::TYPE_EXPENSE)
            ->whereYear('expenses.date', $previousMonth->year)
            ->whereMonth('expenses.date', $previousMonth->month)
            ->sum('expenses.amount');

        $changeAmount = $totalSpent - $previousTotalSpent;

        return [
            'month' => $filter->month,
            'year' => $filter->year,
            'total_spent' => $totalSpent,
            'transactions_count' => $transactionsCount,
            'average_transaction' => $transactionsCount > 0 ? (int) round($totalSpent / $transactionsCount) : 0,
            'top_category' => $categorySummary->isNotEmpty()
                ? [
                    'category_id' => (int) $categorySummary[0]->category_id,
                    'category_name' => (string) $categorySummary[0]->category_name,
                    'total_spent' => (int) $categorySummary[0]->total_spent,
                ]
                : null,
            'highest_expense' => $highestExpense
                ? [
                    'id' => (int) $highestExpense->id,
                    'title' => (string) $highestExpense->title,
                    'amount' => (int) $highestExpense->amount,
                    'date' => (string) $highestExpense->date,
                    'category_name' => (string) optional($highestExpense->category)->name,
                ]
                : null,
            'recent_expenses' => $recentExpenses
                ->map(fn (Expense $expense) => [
                    'id' => (int) $expense->id,
                    'title' => (string) $expense->title,
                    'amount' => (int) $expense->amount,
                    'date' => (string) $expense->date,
                    'category_name' => (string) optional($expense->category)->name,
                ])
                ->values()
                ->all(),
            'trend' => [
                'previous_month' => $previousMonth->month,
                'previous_year' => $previousMonth->year,
                'previous_total_spent' => $previousTotalSpent,
                'change_amount' => $changeAmount,
                'change_percentage' => $previousTotalSpent > 0
                    ? round(($changeAmount / $previousTotalSpent) * 100, 2)
                    : null,
            ],
            'categories' => $categorySummary
                ->map(function ($item) use ($totalSpent) {
                    $categoryTotal = (int) $item->total_spent;

                    return [
                        'category_id' => (int) $item->category_id,
                        'category_name' => (string) $item->category_name,
                        'total_spent' => $categoryTotal,
                        'percentage' => $totalSpent > 0
                            ? round(($categoryTotal / $totalSpent) * 100, 2)
                            : 0,
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    public function get(Expense $expense, int $userId): Expense
    {
        $this->ensureOwnedByUser($expense, $userId);

        return $expense->load('category');
    }

    public function update(Expense $expense, ExpenseData $data): Expense
    {
        $this->ensureOwnedByUser($expense, $data->userId);

        return DB::transaction(function () use ($expense, $data) {
            $expense->update($data->toArray());

            return $expense->refresh()->load('category');
        });
    }

    public function delete(Expense $expense, int $userId): void
    {
        $this->ensureOwnedByUser($expense, $userId);

        DB::transaction(function () use ($expense) {
            $expense->delete();
        });
    }

    private function ensureOwnedByUser(Expense $expense, int $userId): void
    {
        if ((int) $expense->user_id !== $userId) {
            throw new AuthorizationException('You are not allowed to access this expense.');
        }
    }
}
