<?php

declare(strict_types=1);

namespace App\Domains\Order\PrepareEditOrder;

use App\Domains\Shared\UseCaseError;
use App\Models\Order;
use App\Models\OrderStatus;

final class PrepareEditOrderUseCase
{
    public function __invoke(int $id): PrepareEditOrderUseCaseOutput
    {
        $order = Order::query()->with(['status', 'user'])->find($id);
        if ($order === null) {
            return new PrepareEditOrderUseCaseOutput(error: UseCaseError::NotFound);
        }

        return new PrepareEditOrderUseCaseOutput($order, OrderStatus::all());
    }
}
