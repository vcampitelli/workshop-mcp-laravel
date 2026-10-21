<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Domains\Order\ListOrders\ListOrdersUseCase;
use App\Domains\Order\ListOrders\ListOrdersUseCaseInput;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

class ListOrders extends ValidationTool
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
    public function handle(Request $request, ListOrdersUseCase $useCase): ResponseFactory
    {
        $output = $useCase(ListOrdersUseCaseInput::fromArray($request->all()));

        return Response::structured(['orders' => $output->orders->toArray()]);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return $this->validationToSchema->map(
            ListOrdersUseCaseInput::rules()
        );
    }

    /**
     * Define the output schema for this tool's results.
     *
     * @return array<string, mixed>
     */
    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'orders' => $schema->array()->items(
                $this->databaseToSchema->mapModel(new Order, [
                    'status' => new OrderStatus,
                    'user' => new User,
                ]),
            ),
        ];
    }
}
