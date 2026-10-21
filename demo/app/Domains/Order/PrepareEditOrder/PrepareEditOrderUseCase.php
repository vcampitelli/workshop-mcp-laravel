<?php

declare(strict_types=1);

namespace App\Domains\Order\PrepareEditOrder;

use App\Domains\Shared\UseCaseError;
use App\Models\OrderStatus;
use App\Models\User;

final class PrepareEditOrderUseCase
{
    public function __invoke(int $id, User $user): PrepareEditOrderUseCaseOutput
    {
        $order = $user->orders()->with(['status', 'user'])->find($id);
        if ($order === null) {
            return new PrepareEditOrderUseCaseOutput(error: UseCaseError::NotFound);
        }

        return new PrepareEditOrderUseCaseOutput($order, OrderStatus::all());
    }
}
