<?php

namespace App\Mcp\Tools;

use App\Models\OrderStatus;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class FindStatusId extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Retorna o ID de um status do pedido a partir de seu nome.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request, OrderStatus $model): Response
    {
        $name = $request->get('name');
        $status = $model
            ->where('name', 'LIKE', "%{$name}%")
            ->first();

        if (!$status) {
            return Response::error('Status não encontrado.');
        }

        return Response::text($status->id);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\Contracts\JsonSchema\JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()
                ->required()
                ->description('Nome do status do pedido'),
        ];
    }
}
