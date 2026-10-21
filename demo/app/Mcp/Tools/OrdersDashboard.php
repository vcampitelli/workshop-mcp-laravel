<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Resources\OrdersDashboardApp;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\RendersApp;

#[RendersApp(resource: OrdersDashboardApp::class)]
class OrdersDashboard extends ValidationTool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Exibe um relatório com os pedidos.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        return Response::text('Painel de pedidos carregado.');
    }
}
