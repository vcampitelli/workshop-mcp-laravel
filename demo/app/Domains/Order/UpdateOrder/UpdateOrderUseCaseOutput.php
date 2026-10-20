<?php

declare(strict_types=1);

namespace App\Domains\Order\UpdateOrder;

use App\Domains\Shared\UseCaseError;
use App\Models\Order;

final readonly class UpdateOrderUseCaseOutput
{
    public function __construct(
        public ?Order $order = null,
        public ?UseCaseError $error = null,
    ) {}
}
