<?php

declare(strict_types=1);

namespace App\Mcp\Mappers;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Builder as DatabaseBuilder;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Illuminate\JsonSchema\Types;
use Illuminate\Support\Facades\Schema;

/**
 * @phpstan-type Column array{name: string, type: string, type_name: string, nullable: bool, default: mixed, comment: string|null, length?: int}
 */
class DatabaseToSchema
{
    private JsonSchema $schema;

    /**
     * Objetos já mapeados para evitar reprocessar schemas
     *
     * @var array<string, Types\ObjectType>
     */
    private array $pool = [];

    public function __construct()
    {
        $this->schema = new JsonSchemaTypeFactory;
    }

    /**
     * Retorna o schema de uma model
     *
     * @param  array<string, Model>  $relations  Relacionamentos dessa model
     */
    public function mapModel(Model $model, array $relations = []): Types\ObjectType
    {
        return $this->mapTable(
            table: $model->getTable(),
            connection: $model->getConnectionName() ?? $model->getConnection()->getName() ?? config()->string('database.default'),
            hidden: $model->getHidden(),
            relations: $relations,
        );
    }

    /**
     * Retorna o schema de uma tabela
     *
     * @param  string  $table  Nome da tabela
     * @param  string  $connection  Nome da conexão (ex: mysql)
     * @param  array<string>  $hidden  Colunas a ignorar
     * @param  array<string, Model>  $relations  Relacionamentos dessa model
     */
    public function mapTable(
        string $table,
        string $connection,
        array $hidden = [],
        array $relations = [],
    ): Types\ObjectType {
        $key = "{$connection}:{$table}";

        if (! isset($this->pool[$key])) {
            $schema = Schema::connection($connection);
            $this->pool[$key] = $this->doMapTable($schema, $table, $hidden, $relations);
        }

        return $this->pool[$key];
    }

    /**
     * Retorna o schema de uma tabela
     *
     * @param  DatabaseBuilder  $schema  Schema builder do banco de dados
     * @param  string  $table  Nome da tabela
     * @param  array<string>  $hidden  Colunas a ignorar
     * @param  array<string, Model>  $relations  Relacionamentos dessa model
     */
    protected function doMapTable(
        DatabaseBuilder $schema,
        string $table,
        array $hidden = [],
        array $relations = [],
    ): Types\ObjectType {
        $properties = $this->getColumns($schema, $table);
        if (! empty($hidden)) {
            $properties = \array_diff_key($properties, \array_flip($hidden));
        }
        foreach ($relations as $field => $model) {
            $properties[$field] = $this->mapModel($model);
        }

        return $this->schema->object($properties);
    }

    /**
     * Retorna as colunas de uma tabela
     *
     * @param  DatabaseBuilder  $schema  Schema builder do banco de dados
     * @param  string  $table  Nome da tabela
     * @return array<string, Types\Type>
     */
    protected function getColumns(DatabaseBuilder $schema, string $table): array
    {
        $properties = [];

        /** @var list<Column> $columns */
        $columns = $schema->getColumns($table);
        foreach ($columns as $column) {
            $properties[$column['name']] = $this->mapColumnToSchema($column);
        }

        return $properties;
    }

    /**
     * Map a single column to JSON Schema property
     *
     * @param  Column  $column
     */
    protected function mapColumnToSchema(array $column): Types\Type
    {
        $type = $this->mapTypeFromColumn($column)->required();

        // Add description if comment exists
        if (! empty($column['comment'])) {
            $type->description($column['comment']);
        }
        // @TODO description

        // Handle string lengths
        if (($type instanceof Types\StringType) && (($column['length'] ?? 0) > 0)) {
            $type->max($column['length']);
        }

        // Handle numeric constraints
        if ((\str_ends_with($column['type'], ' unsigned')) &&
            (($type instanceof Types\IntegerType) || ($type instanceof Types\NumberType))) {
            $type->min(0);
        }

        // Handle default values
        $default = $column['default'];
        if ($default !== null && $default !== 'NULL') {
            if ($type instanceof Types\StringType && is_string($default)) {
                $type->default($default);
            } elseif ($type instanceof Types\IntegerType && filter_var($default, FILTER_VALIDATE_INT) !== false) {
                $type->default((int) filter_var($default, FILTER_VALIDATE_INT));
            } elseif ($type instanceof Types\NumberType && is_numeric($default)) {
                $type->default((float) $default);
            } elseif ($type instanceof Types\BooleanType && in_array($default, [true, false, 0, 1, '0', '1'], true)) {
                $type->default((bool) $default);
            }
        }

        // Handle nullable
        $type->nullable($column['nullable']);

        // @TODO checar outros tipos (ex: enum)

        return $type;
    }

    /**
     * Mapeia o tipo de coluna no MySQL para um tipo do JSONSchema
     *
     * @param  array{name: string, type: string, type_name: string}  $column  Dados da coluna retornados pelo Schema Builder
     *
     * @link https://dev.mysql.com/doc/refman/8.4/en/integer-types.html
     */
    protected function mapTypeFromColumn(array $column): Types\Type
    {
        $type = $column['type_name'];
        if (\str_ends_with($column['type'], ' unsigned')) {
            $type .= ' unsigned';
        }

        $type = match ($type) {
            'tinyint' => $this->schema->integer()->min(-128)->max(127),
            'tinyint unsigned' => $this->schema->integer()->min(0)->max(255),

            'smallint' => $this->schema->integer()->min(-32768)->max(32767),
            'smallint unsigned' => $this->schema->integer()->min(0)->max(65535),

            'mediumint' => $this->schema->integer()->min(-8388608)->max(8388607),
            'mediumint unsigned' => $this->schema->integer()->min(0)->max(16777215),

            'int' => $this->schema->integer()->min(-2147483648)->max(2147483647),
            'int unsigned' => $this->schema->integer()->min(0)->max(4294967295),

            'bigint' => $this->schema->integer()->min(\PHP_INT_MIN)->max(\PHP_INT_MAX),
            'bigint unsigned' => $this->schema->integer()->min(0)->max(\PHP_INT_MAX), // na verdade é 2**64-1

            'float', 'double', 'decimal' => $this->schema->number(),
            'float unsigned', 'double unsigned', 'decimal unsigned' => $this->schema->number()->min(0),

            'boolean', 'bool' => $this->schema->boolean(),

            'date' => $this->schema->string()->format('date'),
            'datetime', 'timestamp' => $this->schema->string()->format('date-time'),

            'json' => $this->schema->object(),

            default => $this->schema->string(),
        };

        // @link https://json-schema.org/understanding-json-schema/reference/type#built-in-formats
        if ($type instanceof Types\StringType) {
            $format = match ($column['name']) {
                'domain', 'dominio' => 'hostname',
                'email' => 'email',
                'url', 'uri' => 'uri',
                default => null,
            };
            if ($format !== null) {
                $type->format($format);
            }
        }

        return $type;
    }
}
