<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Domains\Product\ListProducts\ListProductsUseCase;
use App\Domains\Product\ListProducts\ListProductsUseCaseInput;
use App\Domains\Product\ViewProduct\ViewProductUseCase;
use App\Domains\Shared\UseCaseError;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request, ListProductsUseCase $useCase): JsonResponse
    {
        $output = $useCase(ListProductsUseCaseInput::fromArray($request->all()));

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
