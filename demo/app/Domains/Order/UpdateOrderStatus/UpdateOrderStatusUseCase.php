<?php

declare(strict_types=1);

namespace App\Domains\Order\UpdateOrderStatus;

use App\Domains\Shared\UseCaseError;
use App\Models\Order;
use App\Models\OrderStatus;

final class UpdateOrderStatusUseCase
{
    public function __invoke(UpdateOrderStatusUseCaseInput $input): UpdateOrderStatusUseCaseOutput
    {
        $order = Order::query()->find($input->orderId);
        if ($order === null) {
            return new UpdateOrderStatusUseCaseOutput(error: UseCaseError::NotFound);
        }
        if (! OrderStatus::query()->whereKey($input->statusId)->exists()) {
            return new UpdateOrderStatusUseCaseOutput(error: UseCaseError::InvalidInput);
        }
        $order->update(['status_id' => $input->statusId]);

        return new UpdateOrderStatusUseCaseOutput($order->load(['status', 'user']));
    }
}
