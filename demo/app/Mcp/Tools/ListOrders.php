<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Http\Controllers\Public\OrderController;
use App\Http\Validation\OrderValidation;
use App\Mcp\Mappers\DatabaseToSchema;
use App\Mcp\Mappers\ValidationToSchema;
use App\Models\Order;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Http\Request as HttpRequest;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

class ListOrders extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Retorna os pedidos realizados na loja.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request, OrderController $controller): ResponseFactory
    {
        $httpRequest = HttpRequest::create(
            uri: '/admin/orders',
            parameters: $request->toArray()
        );

        $response = $controller->index($httpRequest);

        return Response::structured(['orders' => $response->toArray()]);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\Contracts\JsonSchema\JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        /** @var ValidationToSchema $mapper */
        $mapper = \app(ValidationToSchema::class);

        return $mapper->map(
            (new OrderValidation)->getRules()
        );
    }

    /**
     * Define the output schema for this tool's results.
     *
     * @return array<string, mixed>
     */
    public function outputSchema(JsonSchema $schema): array
    {
        /** @var DatabaseToSchema $mapper */
        $mapper = \app(DatabaseToSchema::class);
        return [
            'orders' => $schema->array()->items(
                $mapper->mapModel(new Order(), [
                    'status' => new \App\Models\OrderStatus(),
                    'user' => new \App\Models\User(),
                ]),
            ),
        ];
    }
}
