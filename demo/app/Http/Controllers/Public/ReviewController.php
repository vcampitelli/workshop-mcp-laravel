<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Domains\Review\ListProductReviews\ListProductReviewsUseCase;
use App\Domains\Review\ListProductReviews\ListProductReviewsUseCaseInput;
use App\Domains\Shared\UseCaseError;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request, Product $product, ListProductReviewsUseCase $useCase): JsonResponse
    {
        $output = $useCase(ListProductReviewsUseCaseInput::fromArray(array_replace($request->all(), ['product_id' => $product->id])));
        if ($output->error !== null) {
            abort($output->error === UseCaseError::NotFound ? 404 : 422);
        }

        return response()->json($output->reviews);
    }
}
