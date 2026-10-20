<?php

declare(strict_types=1);

namespace App\Domains\Order\UpdateOrderStatus;

use App\Domains\Shared\UseCaseError;
use App\Models\Order;
use App\Models\OrderStatus;

final class UpdateOrderStatusUseCase
{
    public function __invoke(int $orderId, int $statusId): UpdateOrderStatusUseCaseOutput
    {
        $order = Order::query()->find($orderId);
        if ($order === null) {
            return new UpdateOrderStatusUseCaseOutput(error: UseCaseError::NotFound);
        }
        if (! OrderStatus::query()->whereKey($statusId)->exists()) {
            return new UpdateOrderStatusUseCaseOutput(error: UseCaseError::InvalidInput);
        }
        $order->update(['status_id' => $statusId]);

        return new UpdateOrderStatusUseCaseOutput($order->load(['status', 'user']));
    }
}
