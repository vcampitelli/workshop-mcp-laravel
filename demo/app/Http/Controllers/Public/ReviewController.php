<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Domains\Review\ListProductReviews\ListProductReviewsUseCase;
use App\Domains\Review\ListProductReviews\ListProductReviewsUseCaseInput;
use App\Domains\Shared\UseCaseError;
use App\Http\Controllers\Controller;
use App\Http\Validation\ReviewListValidation;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request, Product $product, ListProductReviewsUseCase $useCase): JsonResponse
    {
        $request->validate((new ReviewListValidation)->getRules());
        $output = $useCase(new ListProductReviewsUseCaseInput(
            productId: $product->id,
            userId: $request->has('user_id') ? $request->integer('user_id') : null,
            rating: $request->has('rating') ? $request->integer('rating') : null,
        ));
        if ($output->error !== null) {
            abort($output->error === UseCaseError::NotFound ? 404 : 422);
        }

        return response()->json($output->reviews);
    }
}
