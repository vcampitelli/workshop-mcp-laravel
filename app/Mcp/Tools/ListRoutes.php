<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\JsonSchema as JsonSchemaFactory;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Illuminate\Support\Facades\Artisan;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @link https://github.com/laravel/boost/blob/main/src/Mcp/Tools/ListRoutes.php
 */
class ListRoutes extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Lista as rotas da API.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): ResponseFactory
    {
        $options = [
            '--except-path'=> 'mcp/',
            '--except-vendor' => true,
            '--json' => true,
            '--no-ansi' => true,
            '--no-interaction' => true,
        ];

        $output = $this->artisan('route:list', $options);
        $output = \json_decode($output, true);
        return Response::structured(['routes' => $output]);
    }

    /**
     * Define the output schema for this tool's results.
     *
     * @return array<string, mixed>
     */
    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'routes' => $schema->array()->items(
                $schema->object([
                    'domain' => $schema->string()
                        ->description('Optional domain for this route')
                        ->nullable(),

                    'method' => $schema->string()
                        ->description('Allowed HTTP methods, separated by "|" (e.g.: GET|HEAD)')
                        ->required(),

                    'uri' => $schema->string()
                        ->description('URI path')
                        ->required(),

                    'name' => $schema->string()
                        ->description('Optional name for this route')
                        ->nullable(),

                    'action' => $schema->string()
                        ->description(
                            'Class name for the Controller in the format "Fully qualified name@method" (e.g: "App\\\HomeControlller@index") or the string "Closure" for routes defined using Closures'
                        )
                        ->required(),

                    'middleware' => $schema->array()
                        ->items($schema->string())
                        ->description('middleware')
                        ->required(),
                ])
            )
        ];
    }

    /**
     * @param  array<string|bool>  $options
     */
    protected function artisan(string $command, array $options = []): string
    {
        $output = new BufferedOutput();
        $response = Artisan::call($command, $options, $output);
        if ($response !== Command::SUCCESS) {
            return 'Failed to list routes: '.$output->fetch();
        }

        return trim($output->fetch());
    }
}
