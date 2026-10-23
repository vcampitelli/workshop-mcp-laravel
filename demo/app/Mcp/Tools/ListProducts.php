<?php

namespace App\Mcp\Tools;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Lista produtos da loja.')]
class ListProducts extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): \Generator
    {
        $query = Product::limit(10);
        $total = (clone $query)->count();

        /** @var Product $product */
        foreach ($query->cursor() as $i => $product) {
            yield Response::notification('processing/progress', [
                 'current' => $i + 1,
                 'total' => $total,
                 'product' => $product->name,
            ]);
            sleep(1);
        }
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            //
        ];
    }
}
