<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class FindUserId extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Retorna o ID de um usuário a partir de seu nome ou email.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request, User $model): Response
    {
        $nameOrEmail = $request->get('name_or_email');
        $user = $model
            ->where('email', 'LIKE', "%{$nameOrEmail}%")
            ->orWhere('name', 'LIKE', "%{$nameOrEmail}%")
            ->first();

        if (!$user) {
            return Response::error('Usuário não encontrado.');
        }

        return Response::text((string) $user->id);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\Contracts\JsonSchema\JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name_or_email' => $schema->string()
                ->required()
                ->description('Nome ou email do usuário'),
        ];
    }
}
