<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Mappers\DatabaseToSchema;
use App\Mcp\Mappers\ValidationToSchema;
use Laravel\Mcp\Server\Tool;

class ValidationTool extends Tool
{
    public function __construct(
        protected readonly ValidationToSchema $validationToSchema,
        protected readonly DatabaseToSchema $databaseToSchema,
    ) {}
}
