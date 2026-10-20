<?php

declare(strict_types=1);

namespace App\Domains\Order\PrepareCreateOrder;

use App\Models\OrderStatus;

final class PrepareCreateOrderUseCase
{
    public function __invoke(): PrepareCreateOrderUseCaseOutput
    {
        return new PrepareCreateOrderUseCaseOutput(OrderStatus::all());
    }
}
