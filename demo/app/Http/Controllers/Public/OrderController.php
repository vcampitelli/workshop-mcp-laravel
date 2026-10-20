<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Validation\OrderListValidation;
use App\Http\Validation\OrderStoreValidation;
use App\Http\Validation\OrderUpdateValidation;
use App\Http\Validation\OrderStatusValidation;
use App\Models\Order;
use App\Models\OrderStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Collection;

class OrderController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return ['auth:api'];
    }

    public function index(Request $request): Collection
    {
        $query = Order::query();

        $filters = $request->validate(
            (new OrderListValidation)->getRules()
        );

        if (!empty($filters['after'])) {
            $query->where('created_at', '>=', $filters['after']);
        }

        if (!empty($filters['before'])) {
            $query->where('created_at', '<=', $filters['before']);
        }

        if (!empty($filters['status_id'])) {
            $query->where('status_id', '=', $filters['status_id']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', '=', $filters['user_id']);
        }

        if (isset($filters['amount_lt'])) {
            $query->where('total_amount', '<=', $filters['amount_lt']);
        }

        if (isset($filters['amount_gt'])) {
            $query->where('total_amount', '>=', $filters['amount_gt']);
        }

        return $query
            ->with(['status', 'user'])
            ->get();
    }

    public function create(): array
    {
        return ['statuses' => OrderStatus::all()];
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate((new OrderStoreValidation)->getRules());
        $order = Order::create($data);

        return response()->json($order->load(['status', 'user']), 201);
    }

    public function show(Order $order): Order
    {
        return $order->load(['status', 'user']);
    }

    public function edit(Order $order): array
    {
        return [
            'order' => $order->load(['status', 'user']),
            'statuses' => OrderStatus::all(),
        ];
    }

    public function update(Request $request, Order $order): Order
    {
        $data = $request->validate((new OrderUpdateValidation)->getRules());
        $order->update($data);

        return $order->load(['status', 'user']);
    }

    public function destroy(Order $order): JsonResponse
    {
        $order->delete();

        return response()->json(null, 204);
    }

    public function updateStatus(Request $request, Order $order): Order
    {
        $data = $request->validate((new OrderStatusValidation)->getRules());
        $order->update($data);

        return $order->load(['status', 'user']);
    }
}
