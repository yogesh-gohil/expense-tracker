<?php

namespace App\Builder;

use Illuminate\Database\Eloquent\Builder;

class IncomeBuilder extends Builder
{
    public function applyFilters(array $filters): self
    {
        if (!empty($filters['user_id'])) {
            $this->where('user_id', (int) $filters['user_id']);
        }

        if (!empty($filters['category_id'])) {
            $this->where('category_id', (int) $filters['category_id']);
        }

        if (!empty($filters['search'])) {
            $this->where('title', 'LIKE', '%'.$filters['search'].'%');
        }

        if (!empty($filters['year'])) {
            $this->whereYear('date', (int) $filters['year']);
        }

        if (!empty($filters['month'])) {
            $this->whereMonth('date', (int) $filters['month']);
        }

        return $this;
    }
}
