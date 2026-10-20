<?php

declare(strict_types=1);

namespace App\Mcp\Prompts;

use App\Http\Validation\OrderListValidation;
use App\Mcp\Mappers\ValidationToArguments;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

class ListOrders extends Prompt
{
    /**
     * The prompt's title.
     */
    protected string $title = 'Prompt de Pedidos';

    /**
     * The prompt's description.
     */
    protected string $description = 'Lista os pedidos da API.';

    /**
     * Handle the prompt request.
     *
     * @return list<Response>
     */
    public function handle(Request $request): array
    {
        $systemMessage = "You are a helpful assistant that list orders according to a series of optional conditionals. Please use the 'list-orders' tool.";
        $userMessage = $this->buildUserMessageFromRequest($request);

        return [
            Response::text($systemMessage)->asAssistant(),
            Response::text($userMessage),
        ];
    }

    /**
     * Build the user message from the request.
     */
    protected function buildUserMessageFromRequest(Request $request): string
    {
        /** @var array{status_id?: int|string, user_id?: int|string, after?: string, before?: string, amount_lt?: int|float|string, amount_gt?: int|float|string} $validated */
        $validated = $request->validate(
            (new OrderListValidation)->getRules()
        );

        $clausules = [];
        if (! empty($validated['status_id'])) {
            $clausules[] = "com ID de status {$validated['status_id']}";
        }
        if (! empty($validated['user_id'])) {
            $clausules[] = "pelo usuário {$validated['user_id']}";
        }
        if (! empty($validated['after'])) {
            $clausules[] = "após a data {$validated['after']}";
        }
        if (! empty($validated['before'])) {
            $clausules[] = "antes da data {$validated['before']}";
        }
        if (! empty($validated['amount_lt'])) {
            $clausules[] = "com preço menor que R\${$validated['amount_lt']}";
        }
        if (! empty($validated['amount_gt'])) {
            $clausules[] = "com preço maior que R\${$validated['amount_gt']}";
        }

        return 'Quais os pedidos realizados '.implode(', ', $clausules).'?';
    }

    /**
     * Get the prompt's arguments.
     *
     * @return array<int, Argument>
     */
    public function arguments(): array
    {
        /** @var ValidationToArguments $mapper */
        $mapper = \app(ValidationToArguments::class);

        return $mapper->map(
            (new OrderListValidation)->getRules()
        );
    }
}
