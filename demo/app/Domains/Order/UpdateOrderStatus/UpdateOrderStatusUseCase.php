<?php

declare(strict_types=1);

namespace App\Domains\Order\UpdateOrderStatus;

use App\Domains\Shared\UseCaseError;
use App\Models\OrderStatus;
use App\Models\User;

final class UpdateOrderStatusUseCase
{
    public function __invoke(UpdateOrderStatusUseCaseInput $input, User $user): UpdateOrderStatusUseCaseOutput
    {
        $order = $user->orders()->find($input->orderId);
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
