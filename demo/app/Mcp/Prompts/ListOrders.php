<?php

declare(strict_types=1);

namespace App\Mcp\Prompts;

use App\Domains\Order\ListOrders\ListOrdersUseCaseInput;
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
        $input = ListOrdersUseCaseInput::fromArray($request->all());

        $clausules = [];
        if ($input->statusId !== null) {
            $clausules[] = "com ID de status {$input->statusId}";
        }
        if ($input->after !== null) {
            $clausules[] = "após a data {$input->after}";
        }
        if ($input->before !== null) {
            $clausules[] = "antes da data {$input->before}";
        }
        if ($input->amountLt !== null) {
            $clausules[] = "com preço menor que R\${$input->amountLt}";
        }
        if ($input->amountGt !== null) {
            $clausules[] = "com preço maior que R\${$input->amountGt}";
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
            ListOrdersUseCaseInput::rules()
        );
    }
}
