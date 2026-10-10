<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Validation\ProductListValidation;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ProductController extends Controller
{
    public function index(Request $request): Collection
    {
        $filters = $request->validate((new ProductListValidation)->getRules());
        $query = Product::query();

        if (isset($filters['name'])) {
            $query->where('name', 'like', '%'.$filters['name'].'%');
        }

        foreach (['product_category_id', 'product_status_id'] as $field) {
            if (isset($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }

        if (isset($filters['price_lt'])) {
            $query->where('price', '<=', $filters['price_lt']);
        }

        if (isset($filters['price_gt'])) {
            $query->where('price', '>=', $filters['price_gt']);
        }

        return $query->with('category')->get();
    }

    public function show(Product $product): Product
    {
        return $product->load(['category', 'productVariants.productAttribute']);
    }
}
