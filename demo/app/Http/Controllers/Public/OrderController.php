<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Domains\Order\CreateOrder\CreateOrderUseCase;
use App\Domains\Order\CreateOrder\CreateOrderUseCaseInput;
use App\Domains\Order\DeleteOrder\DeleteOrderUseCase;
use App\Domains\Order\ListOrders\ListOrdersUseCase;
use App\Domains\Order\ListOrders\ListOrdersUseCaseInput;
use App\Domains\Order\PrepareCreateOrder\PrepareCreateOrderUseCase;
use App\Domains\Order\PrepareEditOrder\PrepareEditOrderUseCase;
use App\Domains\Order\UpdateOrder\UpdateOrderUseCase;
use App\Domains\Order\UpdateOrder\UpdateOrderUseCaseInput;
use App\Domains\Order\UpdateOrderStatus\UpdateOrderStatusUseCase;
use App\Domains\Order\ViewOrder\ViewOrderUseCase;
use App\Domains\Shared\UseCaseError;
use App\Http\Controllers\Controller;
use App\Http\Validation\OrderListValidation;
use App\Http\Validation\OrderStatusValidation;
use App\Http\Validation\OrderStoreValidation;
use App\Http\Validation\OrderUpdateValidation;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class OrderController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return ['auth:api'];
    }

    public function index(Request $request, ListOrdersUseCase $useCase): JsonResponse
    {
        $request->validate((new OrderListValidation)->getRules());
        $output = $useCase(new ListOrdersUseCaseInput(
            statusId: $request->has('status_id') ? $request->integer('status_id') : null,
            userId: $request->has('user_id') ? $request->integer('user_id') : null,
            after: $request->has('after') ? $request->string('after')->toString() : null,
            before: $request->has('before') ? $request->string('before')->toString() : null,
            amountLt: $request->has('amount_lt') ? $request->float('amount_lt') : null,
            amountGt: $request->has('amount_gt') ? $request->float('amount_gt') : null,
        ));

        return response()->json($output->orders);
    }

    public function create(PrepareCreateOrderUseCase $useCase): JsonResponse
    {
        return response()->json(['statuses' => $useCase()->statuses]);
    }

    public function store(Request $request, CreateOrderUseCase $useCase): JsonResponse
    {
        $request->validate((new OrderStoreValidation)->getRules());
        $output = $useCase(new CreateOrderUseCaseInput(
            userId: $request->integer('user_id'),
            statusId: $request->integer('status_id'),
            totalAmount: $request->float('total_amount'),
        ));
        if ($output->error !== null) {
            abort($output->error === UseCaseError::NotFound ? 404 : 422);
        }

        return response()->json($output->order, 201);
    }

    public function show(Order $order, ViewOrderUseCase $useCase): JsonResponse
    {
        $output = $useCase($order->id);
        if ($output->error !== null) {
            abort($output->error === UseCaseError::NotFound ? 404 : 422);
        }

        return response()->json($output->order);
    }

    public function edit(Order $order, PrepareEditOrderUseCase $useCase): JsonResponse
    {
        $output = $useCase($order->id);
        if ($output->error !== null) {
            abort($output->error === UseCaseError::NotFound ? 404 : 422);
        }

        return response()->json(['order' => $output->order, 'statuses' => $output->statuses]);
    }

    public function update(Request $request, Order $order, UpdateOrderUseCase $useCase): JsonResponse
    {
        $request->validate((new OrderUpdateValidation)->getRules());
        $output = $useCase(new UpdateOrderUseCaseInput(
            orderId: $order->id,
            userId: $request->has('user_id') ? $request->integer('user_id') : null,
            statusId: $request->has('status_id') ? $request->integer('status_id') : null,
            totalAmount: $request->has('total_amount') ? $request->float('total_amount') : null,
        ));
        if ($output->error !== null) {
            abort($output->error === UseCaseError::NotFound ? 404 : 422);
        }

        return response()->json($output->order);
    }

    public function destroy(Order $order, DeleteOrderUseCase $useCase): JsonResponse
    {
        $output = $useCase($order->id);
        if ($output->error !== null) {
            abort($output->error === UseCaseError::NotFound ? 404 : 422);
        }

        return response()->json(null, 204);
    }

    public function updateStatus(Request $request, Order $order, UpdateOrderStatusUseCase $useCase): JsonResponse
    {
        $request->validate((new OrderStatusValidation)->getRules());
        $output = $useCase($order->id, $request->integer('status_id'));
        if ($output->error !== null) {
            abort($output->error === UseCaseError::NotFound ? 404 : 422);
        }

        return response()->json($output->order);
    }
}
