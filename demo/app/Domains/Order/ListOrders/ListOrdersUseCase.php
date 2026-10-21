<?php

declare(strict_types=1);

namespace App\Domains\Order\ListOrders;

use App\Models\User;

final class ListOrdersUseCase
{
    public function __invoke(ListOrdersUseCaseInput $input, User $user): ListOrdersUseCaseOutput
    {
        $query = $user->orders();

        if ($input->statusId !== null) {
            $query->where('status_id', '=', $input->statusId);
        }

        if ($input->after !== null) {
            $query->where('created_at', '>=', $input->after);
        }

        if ($input->before !== null) {
            $query->where('created_at', '<=', $input->before);
        }

        if ($input->amountLt !== null) {
            $query->where('total_amount', '<=', $input->amountLt);
        }

        if ($input->amountGt !== null) {
            $query->where('total_amount', '>=', $input->amountGt);
        }

        return new ListOrdersUseCaseOutput($query->with(['status', 'user'])->get());
    }
}
