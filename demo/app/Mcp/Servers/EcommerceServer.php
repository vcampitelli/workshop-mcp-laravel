<?php

declare(strict_types=1);

namespace App\Mcp\Servers;

use App\Mcp\Prompts;
use App\Mcp\Resources;
use App\Mcp\Resources\OrdersDashboardApp;
use App\Mcp\Tools;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Meu Ecommerce Server')]
#[Version('0.0.1')]
#[Instructions('Providencia informações sobre produtos e pedidos de uma loja.')]
class EcommerceServer extends Server
{
    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Tool>>
     */
    protected array $tools = [
        Tools\FindStatusId::class,
        Tools\FindUserId::class,
        Tools\ListOrders::class,
        Tools\ListProducts::class,
        Tools\OrdersDashboard::class,
    ];

    /**
     * The resources registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Resource>>
     */
    protected array $resources = [
        Resources\Guidelines::class,
        OrdersDashboardApp::class,
    ];

    /**
     * The prompts registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Prompt>>
     */
    protected array $prompts = [
        Prompts\ListOrders::class,
    ];
}
