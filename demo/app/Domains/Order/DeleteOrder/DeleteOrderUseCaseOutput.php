<?php

declare(strict_types=1);

namespace App\Domains\Order\DeleteOrder;

use App\Domains\Shared\UseCaseError;

final readonly class DeleteOrderUseCaseOutput
{
    public function __construct(
        public bool $deleted = false,
        public ?UseCaseError $error = null,
    ) {}
}
