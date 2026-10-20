<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Domains\Product\ListProducts\ListProductsUseCase;
use App\Domains\Product\ListProducts\ListProductsUseCaseInput;
use App\Domains\Product\ViewProduct\ViewProductUseCase;
use App\Domains\Shared\UseCaseError;
use App\Http\Controllers\Controller;
use App\Http\Validation\ProductListValidation;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request, ListProductsUseCase $useCase): JsonResponse
    {
        $request->validate((new ProductListValidation)->getRules());
        $output = $useCase(new ListProductsUseCaseInput(
            name: $request->has('name') ? $request->string('name')->toString() : null,
            categoryId: $request->has('product_category_id') ? $request->integer('product_category_id') : null,
            statusId: $request->has('product_status_id') ? $request->integer('product_status_id') : null,
            priceLt: $request->has('price_lt') ? $request->float('price_lt') : null,
            priceGt: $request->has('price_gt') ? $request->float('price_gt') : null,
        ));

        return response()->json($output->products);
    }

    public function show(Product $product, ViewProductUseCase $useCase): JsonResponse
    {
        $output = $useCase($product->id);
        if ($output->error !== null) {
            abort($output->error === UseCaseError::NotFound ? 404 : 422);
        }

        return response()->json($output->product);
    }
}
