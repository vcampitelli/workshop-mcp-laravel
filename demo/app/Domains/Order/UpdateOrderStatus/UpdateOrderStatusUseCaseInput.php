<?php

declare(strict_types=1);

namespace App\Domains\Order\UpdateOrderStatus;

use App\Domains\Shared\UseCaseInput;
use App\Rules\TitleAttribute;
use Illuminate\Contracts\Validation\ValidationRule;

/** @extends UseCaseInput<array{order_id: int|string, status_id: int|string}> */
final readonly class UpdateOrderStatusUseCaseInput extends UseCaseInput
{
    public function __construct(
        public int $orderId,
        public int $statusId,
    ) {}

    /** @return array<string, list<string|ValidationRule>> */
    public static function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'min:1'],
            'status_id' => ['required', 'integer', 'exists:order_statuses,id', new TitleAttribute('ID do status do pedido')],
        ];
    }

    /** @param array{order_id: int|string, status_id: int|string} $data */
    protected static function fromValidatedArray(array $data): static
    {
        return new self(
            orderId: (int) $data['order_id'],
            statusId: (int) $data['status_id'],
        );
    }
}
