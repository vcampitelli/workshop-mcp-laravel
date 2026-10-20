<?php

declare(strict_types=1);

namespace App\Domains\Order\UpdateOrder;

use App\Domains\Shared\UseCaseInput;
use App\Rules\TitleAttribute;
use Illuminate\Contracts\Validation\ValidationRule;

/** @extends UseCaseInput<array{order_id: int|string, user_id?: int|string, status_id?: int|string, total_amount?: int|float|string}> */
final readonly class UpdateOrderUseCaseInput extends UseCaseInput
{
    public function __construct(
        public int $orderId,
        public ?int $userId = null,
        public ?int $statusId = null,
        public ?float $totalAmount = null,
    ) {}

    /** @return array<string, list<string|ValidationRule>> */
    public static function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'min:1'],
            'user_id' => ['sometimes', 'required', 'integer', 'exists:users,id,deleted_at,NULL', new TitleAttribute('ID do usuário')],
            'status_id' => ['sometimes', 'required', 'integer', 'exists:order_statuses,id', new TitleAttribute('ID do status do pedido')],
            'total_amount' => ['sometimes', 'required', 'numeric', 'min:0', new TitleAttribute('Valor total do pedido')],
        ];
    }

    /** @param array{order_id: int|string, user_id?: int|string, status_id?: int|string, total_amount?: int|float|string} $data */
    protected static function fromValidatedArray(array $data): static
    {
        return new self(
            orderId: (int) $data['order_id'],
            userId: isset($data['user_id']) ? (int) $data['user_id'] : null,
            statusId: isset($data['status_id']) ? (int) $data['status_id'] : null,
            totalAmount: isset($data['total_amount']) ? (float) $data['total_amount'] : null,
        );
    }
}
