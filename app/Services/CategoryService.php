<?php

namespace App\Services;

use App\DTO\Category\CategoryData;
use App\DTO\Category\CategoryFilterData;
use App\Models\Category;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    public function list(CategoryFilterData $filter): Collection|LengthAwarePaginator
    {
        $query = Category::query()
            ->where('user_id', $filter->userId)
            ->latest();

        if ($filter->type !== null) {
            $query->where('type', $filter->type);
        }

        if ($filter->search !== null) {
            $query->where('name', 'LIKE', '%'.$filter->search.'%');
        }

        if ($filter->limit === 'all') {
            return $query->get();
        }

        return $query->paginate($filter->limit);
    }

    public function create(CategoryData $data): Category
    {
        return DB::transaction(function () use ($data) {
            return Category::query()->create($data->toArray());
        });
    }

    public function get(Category $category, int $userId): Category
    {
        $this->ensureOwnedByUser($category, $userId);

        return $category;
    }

    public function update(Category $category, CategoryData $data): Category
    {
        $this->ensureOwnedByUser($category, $data->userId);

        return DB::transaction(function () use ($category, $data) {
            $category->update($data->toArray());

            return $category->refresh();
        });
    }

    public function delete(Category $category, int $userId): void
    {
        $this->ensureOwnedByUser($category, $userId);

        DB::transaction(function () use ($category) {
            $category->delete();
        });
    }

    private function ensureOwnedByUser(Category $category, int $userId): void
    {
        if ((int) $category->user_id !== $userId) {
            throw new AuthorizationException('You are not allowed to access this category.');
        }
    }
}
