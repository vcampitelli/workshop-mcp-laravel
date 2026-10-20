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
use App\Domains\Order\UpdateOrderStatus\UpdateOrderStatusUseCaseInput;
use App\Domains\Order\ViewOrder\ViewOrderUseCase;
use App\Domains\Shared\UseCaseError;
use App\Http\Controllers\Controller;
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
        $output = $useCase(ListOrdersUseCaseInput::fromArray($request->all()));

        return response()->json($output->orders);
    }

    public function create(PrepareCreateOrderUseCase $useCase): JsonResponse
    {
        return response()->json(['statuses' => $useCase()->statuses]);
    }

    public function store(Request $request, CreateOrderUseCase $useCase): JsonResponse
    {
        $output = $useCase(CreateOrderUseCaseInput::fromArray($request->all()));
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
        $output = $useCase(UpdateOrderUseCaseInput::fromArray(array_replace($request->all(), ['order_id' => $order->id])));
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
        $output = $useCase(UpdateOrderStatusUseCaseInput::fromArray(array_replace($request->all(), ['order_id' => $order->id])));
        if ($output->error !== null) {
            abort($output->error === UseCaseError::NotFound ? 404 : 422);
        }

        return response()->json($output->order);
    }
}
