<?php

declare(strict_types=1);

namespace App\Domains\Order\UpdateOrder;

final readonly class UpdateOrderUseCaseInput
{
    public function __construct(
        public int $orderId,
        public ?int $userId = null,
        public ?int $statusId = null,
        public ?float $totalAmount = null,
    ) {}
}
