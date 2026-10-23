<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Mappers\DatabaseToSchema;
use App\Mcp\Mappers\ValidationToSchema;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\Server\Tool;

abstract class ValidationTool extends Tool
{
    public function __construct(
        protected readonly ValidationToSchema $validationToSchema,
        protected readonly DatabaseToSchema $databaseToSchema,
    ) {}

    protected function user(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        return $user;
    }
}
