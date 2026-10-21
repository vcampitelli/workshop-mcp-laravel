<?php

declare(strict_types=1);

namespace App\Domains\Order\CreateOrder;

use App\Domains\Shared\UseCaseError;
use App\Models\OrderStatus;
use App\Models\User;

final class CreateOrderUseCase
{
    public function __invoke(CreateOrderUseCaseInput $input, User $user): CreateOrderUseCaseOutput
    {
        if ($input->totalAmount < 0 || ! is_finite($input->totalAmount)
            || ! OrderStatus::query()->whereKey($input->statusId)->exists()) {
            return new CreateOrderUseCaseOutput(error: UseCaseError::InvalidInput);
        }
        $order = $user->orders()->create([
            'status_id' => $input->statusId,
            'total_amount' => $input->totalAmount,
        ]);

        return new CreateOrderUseCaseOutput($order->load(['status', 'user']));
    }
}
