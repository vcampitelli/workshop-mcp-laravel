<?php

declare(strict_types=1);

namespace App\Mcp;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Illuminate\JsonSchema\Types;
use Illuminate\Support\Facades\Schema;

class DatabaseToSchema
{
    private JsonSchema $schema;

    public function __construct()
    {
        $this->schema = new JsonSchemaTypeFactory();
    }

    /**
     * Map a database table to a JSON Schema
     */
    public function mapTable(string $table, string $connection): Types\ObjectType
    {
        $properties = [];

        $schema = Schema::connection($connection);
        $columns = $schema->getColumns($table);

        // @TODO
        // $foreignKeys = $schema->getForeignKeys($table);
        foreach ($columns as $column) {
            $properties[$column['name']] = $this->mapColumnToSchema($column);
        }

        return $this->schema->object($properties);
    }

    /**
     * Map a single column to JSON Schema property
     */
    protected function mapColumnToSchema(array $column): Types\Type
    {
        $type = $this->mapType($column)->required();
        $schema = [];

        // Add description if comment exists
        if (!empty($column['comment'])) {
            $type->description($column['comment']);
        }
        // @TODO description

        // Handle string lengths
        if (in_array($column['type_name'], ['varchar', 'char', 'text'])) {
            if (isset($column['length']) && $column['length'] > 0) {
                if ($type instanceof Types\StringType) {
                    $type->max($column['length']);
                }
            }
        }

        $isNumericType = ($type instanceof Types\IntegerType) || ($type instanceof Types\NumberType);

        // Handle numeric constraints
        if (($isNumericType) && (\str_ends_with($column['type'], ' unsigned'))) {
            $type->min(0);
        }

        // Handle default values
        if ((isset($column['default'])) && ($column['default'] !== 'NULL')) {
            $type->default($column['default']);
        }

        // Handle enums
        if ($column['type_name'] === 'enum' && !empty($column['enum_values'])) {
            $schema['enum'] = $column['enum_values'];
        }

        // Handle nullable
        if ($column['nullable'] !== null) {
            $type->nullable($column['nullable']);
        }

        if (!empty($schema)) {
            dd($column, $schema);
        }

        return $type;
    }

    /**
     * Map MySQL type to JSON Schema type
     * @link https://dev.mysql.com/doc/refman/8.4/en/integer-types.html
     */
    protected function mapType(array $column): Types\Type
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

    /**
     * Cast default value to appropriate type
     */
    protected function castDefault(mixed $default, string $type): mixed
    {
        return match ($this->mapType($type)) {
            'integer' => (int) $default,
            'number' => (float) $default,
            'boolean' => (bool) $default,
            'object' => json_decode($default, true),
            default => (string) $default,
        };
    }
}
