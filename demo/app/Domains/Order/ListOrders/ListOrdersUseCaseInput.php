<?php

declare(strict_types=1);

namespace App\Domains\Order\ListOrders;

use App\Domains\Shared\UseCaseInput;
use App\Rules\TitleAttribute;
use Illuminate\Contracts\Validation\ValidationRule;

/** @extends UseCaseInput<array{status_id?: int|string, after?: string, before?: string, amount_lt?: int|float|string, amount_gt?: int|float|string}> */
final readonly class ListOrdersUseCaseInput extends UseCaseInput
{
    public function __construct(
        public ?int $statusId = null,
        public ?string $after = null,
        public ?string $before = null,
        public ?float $amountLt = null,
        public ?float $amountGt = null,
    ) {}

    /** @return array<string, list<string|ValidationRule>> */
    public static function rules(): array
    {
        return [
            'status_id' => ['sometimes', 'integer', 'exists:order_statuses,id', new TitleAttribute('ID do status do pedido')],
            'after' => ['sometimes', 'string', 'date', new TitleAttribute('Pedidos depois dessa data')],
            'before' => ['sometimes', 'string', 'date', new TitleAttribute('Pedidos antes dessa data')],
            'amount_lt' => ['sometimes', 'numeric', new TitleAttribute('Valor menor que')],
            'amount_gt' => ['sometimes', 'numeric', new TitleAttribute('Valor maior que')],
        ];
    }

    /** @param array{status_id?: int|string, after?: string, before?: string, amount_lt?: int|float|string, amount_gt?: int|float|string} $data */
    protected static function fromValidatedArray(array $data): static
    {
        return new self(
            statusId: isset($data['status_id']) ? (int) $data['status_id'] : null,
            after: isset($data['after']) ? (string) $data['after'] : null,
            before: isset($data['before']) ? (string) $data['before'] : null,
            amountLt: isset($data['amount_lt']) ? (float) $data['amount_lt'] : null,
            amountGt: isset($data['amount_gt']) ? (float) $data['amount_gt'] : null,
        );
    }
}
