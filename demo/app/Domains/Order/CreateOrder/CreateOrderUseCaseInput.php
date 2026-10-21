<?php

declare(strict_types=1);

namespace App\Domains\Order\CreateOrder;

use App\Domains\Shared\UseCaseInput;
use App\Rules\TitleAttribute;
use Illuminate\Contracts\Validation\ValidationRule;

/** @extends UseCaseInput<array{status_id: int|string, total_amount: int|float|string}> */
final readonly class CreateOrderUseCaseInput extends UseCaseInput
{
    public function __construct(
        public int $statusId,
        public float $totalAmount,
    ) {}

    /** @return array<string, list<string|ValidationRule>> */
    public static function rules(): array
    {
        return [
            'status_id' => ['required', 'integer', 'exists:order_statuses,id', new TitleAttribute('ID do status do pedido')],
            'total_amount' => ['required', 'numeric', 'min:0', new TitleAttribute('Valor total do pedido')],
        ];
    }

    /** @param array{status_id: int|string, total_amount: int|float|string} $data */
    protected static function fromValidatedArray(array $data): static
    {
        return new self(
            statusId: (int) $data['status_id'],
            totalAmount: (float) $data['total_amount'],
        );
    }
}
