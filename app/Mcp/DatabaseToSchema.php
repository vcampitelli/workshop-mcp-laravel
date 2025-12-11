<?php

declare(strict_types=1);

namespace App\Mcp;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Builder;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Illuminate\JsonSchema\Types;
use Illuminate\JsonSchema\Types\ObjectType;
use Illuminate\Support\Facades\Schema;

class DatabaseToSchema
{
    private JsonSchema $schema;

    /**
     * Objetos já mapeados para evitar reprocessar schemas
     * @var array<string, Types\ObjectType>
     */
    private array $pool = [];

    public function __construct()
    {
        $this->schema = new JsonSchemaTypeFactory();
    }

    /**
     * Retorna o schema de uma model
     * @param Model $model
     * @return Types\ObjectType
     */
    public function mapModel(Model $model): Types\ObjectType
    {
        return $this->mapTable(
            table: $model->getTable(),
            connection: $model->getConnectionName() ?? $model->getConnection()->getName(),
        );
    }

    /**
     * Retorna o schema de uma tabela
     * @param string $table Nome da tabela
     * @param string $connection Nome da conexão (ex: mysql)
     * @return ObjectType
     */
    public function mapTable(string $table, string $connection): Types\ObjectType
    {
        $key = "{$connection}:{$table}";

        if (!isset($this->pool[$key])) {
            $schema = Schema::connection($connection);
            $this->pool[$key] = $this->doMapTable($schema, $table);
        }

        return $this->pool[$key];
    }

    /**
     * Retorna o schema de uma tabela
     * @param Builder $schema Schema builder do banco de dados
     * @param string $table Nome da tabela
     * @return ObjectType
     */
    protected function doMapTable(Builder $schema, string $table): Types\ObjectType
    {
        $properties = $this->getColumns($schema, $table);
        $properties = $this->getForeignKeys($schema, $table, $properties);

        return $this->schema->object($properties);
    }

    /**
     * Retorna as foreign keys de uma tabela
     * @param Builder $schema Schema builder do banco de dados
     * @param string $table Nome da tabela
     */
    protected function getForeignKeys(Builder $schema, string $table, array $properties): array
    {
        /**
         * @var list<array{name: string, columns: string[], foreign_schema: string, foreign_table: string, foreign_columns: string[], on_update: string, on_delete: string}> $foreignKeys
         */
        $foreignKeys = $schema->getForeignKeys($table);
        foreach ($foreignKeys as $key) {
            // Evitando recursão
            if ($key['foreign_table'] === $table) {
                continue;
            }

            // Só lidando com índices de uma única coluna, por enquanto
            if (count($key['columns']) !== 1) {
                continue;
            }

            // @TODO buscar da Model o nome da relação
            if (!\str_ends_with($key['columns'][0], '_id')) {
                continue;
            }
            $column = \substr($key['columns'][0], 0, -3);

            $properties[$column] = $this->doMapTable($schema, $key['foreign_table']);
        }

        return $properties;
    }

    /**
     * Retorna as colunas de uma tabela
     * @param Builder $schema Schema builder do banco de dados
     * @param string $table Nome da tabela
     */
    protected function getColumns(Builder $schema, string $table): array
    {
        $properties = [];

        $columns = $schema->getColumns($table);
        foreach ($columns as $column) {
            $properties[$column['name']] = $this->mapColumnToSchema($column);
        }

        return $properties;
    }

    /**
     * Map a single column to JSON Schema property
     */
    protected function mapColumnToSchema(array $column): Types\Type
    {
        $type = $this->mapTypeFromColumn($column)->required();

        // Add description if comment exists
        if (!empty($column['comment'])) {
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
        if ((isset($column['default'])) && ($column['default'] !== 'NULL')) {
            $type->default($column['default']);
        }

        // Handle nullable
        if ($column['nullable'] !== null) {
            $type->nullable($column['nullable']);
        }

        // @TODO checar outros tipos (ex: enum)

        return $type;
    }

    /**
     * Mapeia o tipo de coluna no MySQL para um tipo do JSONSchema
     * @param array{name: string, type: string, type_name: string} $column Dados da coluna retornados pelo Schema Builder
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

            'int' => $this->schema->integer()->min(2147483648)->max(2147483647),
            'int unsigned' => $this->schema->integer()->min(0)->max(4294967295),

            // como aqui depende da precisão configurada no PHP, optei por não usar o PHP_INT_MAX
            'bigint' => $this->schema->integer()->min(\PHP_INT_MIN)->max(\PHP_INT_MAX),
            'bigint unsigned' => $this->schema->integer()->min(0)->max(\PHP_INT_MAX),

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
