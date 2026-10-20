<?php

declare(strict_types=1);

namespace App\Domains\Order\CreateOrder;

use App\Domains\Shared\UseCaseError;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\User;

final class CreateOrderUseCase
{
    public function __invoke(CreateOrderUseCaseInput $input): CreateOrderUseCaseOutput
    {
        if ($input->totalAmount < 0 || ! is_finite($input->totalAmount)
            || ! User::query()->whereKey($input->userId)->exists()
            || ! OrderStatus::query()->whereKey($input->statusId)->exists()) {
            return new CreateOrderUseCaseOutput(error: UseCaseError::InvalidInput);
        }
        $order = Order::query()->create([
            'user_id' => $input->userId,
            'status_id' => $input->statusId,
            'total_amount' => $input->totalAmount,
        ]);

        return new CreateOrderUseCaseOutput($order->load(['status', 'user']));
    }
}
