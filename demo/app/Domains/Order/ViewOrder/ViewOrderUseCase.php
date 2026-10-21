<?php

declare(strict_types=1);

namespace App\Domains\Order\ViewOrder;

use App\Domains\Shared\UseCaseError;
use App\Models\User;

final class ViewOrderUseCase
{
    public function __invoke(int $id, User $user): ViewOrderUseCaseOutput
    {
        $order = $user->orders()->with(['status', 'user'])->find($id);

        return $order === null
            ? new ViewOrderUseCaseOutput(error: UseCaseError::NotFound)
            : new ViewOrderUseCaseOutput(order: $order);
    }
}
