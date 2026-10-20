<?php

declare(strict_types=1);

namespace App\Domains\Order\PrepareCreateOrder;

use App\Models\OrderStatus;
use Illuminate\Database\Eloquent\Collection;

final readonly class PrepareCreateOrderUseCaseOutput
{
    /** @param Collection<int, OrderStatus> $statuses */
    public function __construct(
        public Collection $statuses,
    ) {}
}
