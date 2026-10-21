<?php

declare(strict_types=1);

namespace App\Domains\Order\DeleteOrder;

use App\Domains\Shared\UseCaseError;
use App\Models\User;

final class DeleteOrderUseCase
{
    public function __invoke(int $id, User $user): DeleteOrderUseCaseOutput
    {
        $order = $user->orders()->find($id);
        if ($order === null) {
            return new DeleteOrderUseCaseOutput(error: UseCaseError::NotFound);
        }
        $order->delete();

        return new DeleteOrderUseCaseOutput(deleted: true);
    }
}
