<?php

declare(strict_types=1);

namespace App\Domains\Order\DeleteOrder;

use App\Domains\Shared\UseCaseError;
use App\Models\Order;

final class DeleteOrderUseCase
{
    public function __invoke(int $id): DeleteOrderUseCaseOutput
    {
        $order = Order::query()->find($id);
        if ($order === null) {
            return new DeleteOrderUseCaseOutput(error: UseCaseError::NotFound);
        }
        $order->delete();

        return new DeleteOrderUseCaseOutput(deleted: true);
    }
}
