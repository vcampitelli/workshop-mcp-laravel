<?php

declare(strict_types=1);

namespace App\Mcp\Mappers;

use App\Rules\TitleAttribute;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Illuminate\JsonSchema\Types;

class ValidationToSchema
{
    private JsonSchema $schema;

    public function __construct()
    {
        $this->schema = new JsonSchemaTypeFactory;
    }

    /**
     * @param  array<string, ValidationRule|list<mixed>|string>  $rules
     * @return array<string, Types\Type>
     */
    public function map(array $rules): array
    {
        $schema = [];

        foreach ($rules as $field => $rule) {
            if (\is_string($rule)) {
                $rule = \explode('|', $rule);
            }

            // @TODO checar outros tipos de validação por classe
            if (! \is_array($rule)) {
                continue;
            }

            $schema[$field] = $this->mapRulesToType($rule);
        }

        return $schema;
    }

    /** @param list<mixed> $rules */
    protected function mapRulesToType(array $rules): Types\Type
    {
        $type = null;
        foreach ($rules as $rule) {
            if (! \is_string($rule)) {
                continue;
            }

            if (($rule === 'numeric') || ($rule === 'decimal')) {
                $type = $this->schema->number();
                break;
            }

            if ((\str_starts_with($rule, 'exists:')) && (\str_ends_with($rule, 'id'))) {
                $type = $this->schema->integer()->min(0);
                break;
            }
        }
        if ($type === null) {
            $type = $this->schema->string();
        }

        foreach ($rules as $rule) {
            if ($rule instanceof TitleAttribute) {
                $type->description($rule->title);

                continue;
            }

            if ($rule === 'date' && $type instanceof Types\StringType) {
                $type->format('date-time');
            }

            if ($rule === 'required') {
                $type->required();
            }
        }

        return $type;
    }
}
