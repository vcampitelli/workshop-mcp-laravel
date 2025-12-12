<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use App\Mcp\Mappers\ModelRelations;
use App\Models\Order;
use App\Models\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;

class Guidelines extends Resource
{
    /**
     * The resource's description.
     */
    protected string $description = <<<'MARKDOWN'
        Orientações de relacionamentos e entidades do sistema.
    MARKDOWN;

    /**
     * The resource's MIME type.
     */
    protected string $mimeType = 'text/markdown';

    /**
     * The resource's URI.
     */
    protected string $uri = 'orders://resources/guidelines';

    /**
     * Handle the resource request.
     */
    public function handle(Request $request): Response
    {
        $text = <<<'MARKDOWN'
# Status do Pedido

Mapeamento da coluna `status_id` do pedido:

MARKDOWN;

        foreach (OrderStatus::all() as $status) {
            $text .= "{$status->id}: {$status->name}\n";
        }

        return Response::text($text);
    }
}
