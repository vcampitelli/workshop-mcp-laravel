<?php

declare(strict_types=1);

namespace App\Mcp\Servers;

use App\Mcp\Prompts;
use App\Mcp\Resources;
use App\Mcp\Tools;
use Laravel\Mcp\Server;

class OrdersServer extends Server
{
    /**
     * The MCP server's name.
     */
    protected string $name = 'Orders Server';

    /**
     * The MCP server's version.
     */
    protected string $version = '0.0.1';

    /**
     * The MCP server's instructions for the LLM.
     */
    protected string $instructions = <<<'MARKDOWN'
        Esse servidor providencia informações sobre os pedidos de uma loja.
    MARKDOWN;

    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Tool>>
     */
    protected array $tools = [
        Tools\FindStatusId::class,
        Tools\FindUserId::class,
        Tools\ListOrders::class,
    ];

    /**
     * The resources registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Resource>>
     */
    protected array $resources = [
        Resources\Guidelines::class,
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
