<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Validation\ReviewListValidation;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReviewController extends Controller
{
    public function index(Request $request, Product $product): Collection
    {
        $filters = $request->validate((new ReviewListValidation)->getRules());
        $query = $product->reviews();

        foreach (['user_id', 'rating'] as $field) {
            if (isset($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }

        return $query->with('user')->get();
    }
}
