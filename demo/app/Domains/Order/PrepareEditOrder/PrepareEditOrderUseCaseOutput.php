<?php

declare(strict_types=1);

namespace App\Domains\Order\PrepareEditOrder;

use App\Domains\Shared\UseCaseError;
use App\Models\Order;
use App\Models\OrderStatus;
use Illuminate\Database\Eloquent\Collection;

final readonly class PrepareEditOrderUseCaseOutput
{
    /** @param Collection<int, OrderStatus>|null $statuses */
    public function __construct(
        public ?Order $order = null,
        public ?Collection $statuses = null,
        public ?UseCaseError $error = null,
    ) {}
}
