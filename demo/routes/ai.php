<?php

use App\Mcp\Servers;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp/orders', Servers\OrdersServer::class);
