<?php

use App\Mcp\Servers;
use Laravel\Mcp\Facades\Mcp;

Mcp::oauthRoutes();

Mcp::web('/mcp', Servers\EcommerceServer::class)
    ->middleware('auth:api');
