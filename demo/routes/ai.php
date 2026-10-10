<?php

use App\Mcp\Servers;
use Laravel\Mcp\Facades\Mcp;
use Laravel\Passport\Http\Middleware\CheckToken;

Mcp::web('/mcp/orders', Servers\OrdersServer::class)
    ->middleware(['auth:api', CheckToken::using('orders:list')]);
