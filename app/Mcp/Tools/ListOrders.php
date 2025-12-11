<?php

namespace App\Mcp\Tools;

use App\Http\Controllers\Admin\OrderController;
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
        return [
            'after' => $schema->string()->format('date-time')->description('Pedidos depois dessa data'),
            'before' => $schema->string()->format('date-time')->description('Pedidos antes dessa data'),
        ];
    }

    /**
     * Define the output schema for this tool's results.
     *
     * @return array<string, mixed>
     */
    public function outputSchema(JsonSchema $schema): array
    {
        $order = new Order();
        // @TODO falar sobre os status e o usuário
//        $status = new \App\Models\OrderStatus();
//        $user = new \App\Models\User();
        $mapper = new \App\Mcp\DatabaseToSchema();
        return [
            'orders' => $schema->array()->items(
                $mapper->mapTable(
                    $order->getTable(),
                    $order->getConnectionName()
                )
            )
        ];
    }
}
