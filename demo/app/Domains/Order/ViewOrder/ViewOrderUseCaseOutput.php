<?php

declare(strict_types=1);

namespace App\Domains\Order\ViewOrder;

use App\Domains\Shared\UseCaseError;
use App\Models\Order;

final readonly class ViewOrderUseCaseOutput
{
    public function __construct(
        public ?Order $order = null,
        public ?UseCaseError $error = null,
    ) {}
}
