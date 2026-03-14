<?php

namespace App\Builder;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;

class IncomeBuilder extends Builder
{
    public function applyFilters(array $filters): self
    {
        $filters = collect($filters)->mapWithKeys(
            fn ($value, $key) => [strtolower((string) $key) => $value]
        );

        if ($filters->get('user_id')) {
            $this->where('user_id', (int) $filters->get('user_id'));
        }

        if ($filters->get('category_id')) {
            $this->where('category_id', (int) $filters->get('category_id'));
        }

        if ($filters->get('search')) {
            $this->where('title', 'LIKE', '%'.$filters->get('search').'%');
        }

        if ($filters->get('year')) {
            $this->whereYear('date', (int) $filters->get('year'));
        }

        if ($filters->get('month')) {
            $this->whereMonth('date', (int) $filters->get('month'));
        }

        if ($filters->get('sort_by') || $filters->get('sort_dir')) {
            $field = $filters->get('sort_by') ? (string) $filters->get('sort_by') : 'created_at';
            $sortDir = $filters->get('sort_dir') ? (string) $filters->get('sort_dir') : 'asc';
            $this->applySort($field, $sortDir);
        }

        return $this;
    }

    public function applySort(string $sortBy, ?string $sortDir = null): self
    {
        $direction = strtolower((string) $sortDir) === 'asc' ? 'asc' : 'desc';
        $sortKey = strtolower($sortBy);

        if ($sortKey === 'category_name') {
            $this->orderBy(
                Category::select('name')
                    ->whereColumn('categories.id', 'incomes.category_id'),
                $direction
            );

            return $this;
        }

        $allowedSorts = ['title', 'amount', 'date', 'created_at'];
        if (in_array($sortKey, $allowedSorts, true)) {
            $this->orderBy($sortKey, $direction);
        }

        return $this;
    }
}
