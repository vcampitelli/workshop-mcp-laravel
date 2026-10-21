<?php

use App\Mcp\Servers;
use Laravel\Mcp\Facades\Mcp;

Mcp::oauthRoutes();

Mcp::web('/mcp/orders', Servers\OrdersServer::class)
    ->middleware('auth:api');
