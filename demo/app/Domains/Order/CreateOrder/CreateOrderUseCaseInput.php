<?php

declare(strict_types=1);

namespace App\Domains\Order\CreateOrder;

final readonly class CreateOrderUseCaseInput
{
    public function __construct(
        public int $userId,
        public int $statusId,
        public float $totalAmount,
    ) {}
}
