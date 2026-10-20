<?php

declare(strict_types=1);

namespace App\Domains\Order\ListOrders;

final readonly class ListOrdersUseCaseInput
{
    public function __construct(
        public ?int $statusId = null,
        public ?int $userId = null,
        public ?string $after = null,
        public ?string $before = null,
        public ?float $amountLt = null,
        public ?float $amountGt = null,
    ) {}
}
