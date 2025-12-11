<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

class ListRoutes extends Prompt
{
    /**
     * The prompt's name.
     */
    protected string $name = 'list-routes';

    /**
     * The prompt's title.
     */
    protected string $title = 'Prompt do Assistente de Rotas';

    /**
     * The prompt's description.
     */
    protected string $description = <<<'MARKDOWN'
        Lista as rotas de uma API.
    MARKDOWN;

    /**
     * Handle the prompt request.
     */
    public function handle(Request $request): array
    {
        $systemMessage = 'You are a helpful assistant that takes a question and finds the most appropriate tool or tools to execute, along with the parameters required to run the tool. The tools are:<|tool|>';
        $systemMessage .= \json_encode([$this->toArray()]);
        $systemMessage .= '<|/tool|>';

        return [
            Response::text($systemMessage)->asAssistant(),
            Response::text('Liste as rotas da API'),
        ];
    }

    /**
     * Get the prompt's arguments.
     *
     * @return array<int, \Laravel\Mcp\Server\Prompts\Argument>
     */
    public function arguments(): array
    {
        return [
            //
        ];
    }
}
