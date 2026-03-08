<?php

namespace App\DTO\Expense;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class ExpenseSummaryFilterData
{
    public function __construct(
        public readonly int $userId,
        public readonly int $month,
        public readonly int $year,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $now = CarbonImmutable::now();
        $month = (int) $request->query('month', $now->month);
        $year = (int) $request->query('year', $now->year);

        if ($month < 1 || $month > 12) {
            $month = $now->month;
        }

        if ($year < 2000 || $year > 3000) {
            $year = $now->year;
        }

        return new self(
            userId: (int) $request->user()->id,
            month: $month,
            year: $year,
        );
    }
}
