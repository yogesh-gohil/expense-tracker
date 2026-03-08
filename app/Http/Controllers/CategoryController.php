<?php

namespace App\Http\Controllers;

use App\DTO\Category\CategoryData;
use App\DTO\Category\CategoryFilterData;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categoryService,
    ) {}

    public function index(Request $request)
    {
        $categories = $this->categoryService->list(CategoryFilterData::fromRequest($request));

        return CategoryResource::collection($categories);
    }

    public function store(CategoryRequest $request)
    {
        $category = $this->categoryService->create(CategoryData::fromRequest($request));

        return new CategoryResource($category);
    }

    public function show(Request $request, Category $category)
    {
        $category = $this->categoryService->get($category, (int) $request->user()->id);

        return new CategoryResource($category);
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $category = $this->categoryService->update($category, CategoryData::fromRequest($request));

        return new CategoryResource($category);
    }

    public function destroy(Request $request, Category $category)
    {
        $this->categoryService->delete($category, (int) $request->user()->id);

        return response()->noContent();
    }
}
