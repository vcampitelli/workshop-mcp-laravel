<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Validation\ProductCategoryListValidation;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ProductCategoryController extends Controller
{
    public function index(Request $request): Collection
    {
        $filters = $request->validate((new ProductCategoryListValidation)->getRules());
        $query = ProductCategory::query();

        if (isset($filters['name'])) {
            $query->where('name', 'like', '%'.$filters['name'].'%');
        }

        if (isset($filters['parent_id'])) {
            $query->where('parent_id', $filters['parent_id']);
        }

        return $query->get();
    }

    public function show(ProductCategory $category): ProductCategory
    {
        return $category->load('products');
    }
}
