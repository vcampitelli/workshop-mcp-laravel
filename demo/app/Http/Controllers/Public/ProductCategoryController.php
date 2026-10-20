<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Domains\ProductCategory\ListProductCategories\ListProductCategoriesUseCase;
use App\Domains\ProductCategory\ViewProductCategory\ViewProductCategoryUseCase;
use App\Domains\Shared\UseCaseError;
use App\Http\Controllers\Controller;
use App\Http\Validation\ProductCategoryListValidation;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function index(Request $request, ListProductCategoriesUseCase $useCase): JsonResponse
    {
        $request->validate((new ProductCategoryListValidation)->getRules());
        $output = $useCase(
            name: $request->has('name') ? $request->string('name')->toString() : null,
            parentId: $request->has('parent_id') ? $request->integer('parent_id') : null,
        );

        return response()->json($output->categories);
    }

    public function show(ProductCategory $category, ViewProductCategoryUseCase $useCase): JsonResponse
    {
        $output = $useCase($category->id);
        if ($output->error !== null) {
            abort($output->error === UseCaseError::NotFound ? 404 : 422);
        }

        return response()->json($output->category);
    }
}
