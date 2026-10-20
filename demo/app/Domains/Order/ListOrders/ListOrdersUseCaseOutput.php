<?php

declare(strict_types=1);

namespace App\Domains\Order\ListOrders;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

final readonly class ListOrdersUseCaseOutput
{
    /** @param Collection<int, Order> $orders */
    public function __construct(
        public Collection $orders,
    ) {}
}
