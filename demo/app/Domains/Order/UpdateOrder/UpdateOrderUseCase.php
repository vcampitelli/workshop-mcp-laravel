<?php

declare(strict_types=1);

namespace App\Domains\Order\UpdateOrder;

use App\Domains\Shared\UseCaseError;
use App\Models\OrderStatus;
use App\Models\User;

final class UpdateOrderUseCase
{
    public function __invoke(UpdateOrderUseCaseInput $input, User $user): UpdateOrderUseCaseOutput
    {
        $order = $user->orders()->find($input->orderId);
        if ($order === null) {
            return new UpdateOrderUseCaseOutput(error: UseCaseError::NotFound);
        }
        if (($input->statusId !== null && ! OrderStatus::query()->whereKey($input->statusId)->exists())
            || ($input->totalAmount !== null && ($input->totalAmount < 0 || ! is_finite($input->totalAmount)))) {
            return new UpdateOrderUseCaseOutput(error: UseCaseError::InvalidInput);
        }
        $order->update(array_filter([
            'status_id' => $input->statusId,
            'total_amount' => $input->totalAmount,
        ], static fn (int|float|null $value): bool => $value !== null));

        return new UpdateOrderUseCaseOutput($order->load(['status', 'user']));
    }
}
