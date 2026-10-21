<?php

declare(strict_types=1);

namespace App\Mcp\Mappers;

use App\Rules\TitleAttribute;
use Closure;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Illuminate\JsonSchema\Types;
use Illuminate\Validation\ValidationRuleParser;
use InvalidArgumentException;

/** @phpstan-type ParsedRule array{name: string, parameters: list<string|null>} */
class ValidationToSchema
{
    private readonly JsonSchema $schema;

    public function __construct()
    {
        $this->schema = new JsonSchemaTypeFactory;
    }

    /**
     * Overrides replace a field's entire schema, including presence and nullability.
     *
     * @param  array<string, string|ValidationRule|Closure|list<string|ValidationRule|Closure>>  $rules
     * @param  array<string, Types\Type>  $overrides
     * @return array<string, Types\Type>
     */
    public function map(array $rules, array $overrides = []): array
    {
        if (array_diff_key($overrides, $rules) !== []) {
            throw new InvalidArgumentException('Schema overrides must reference fields declared in the validation rules.');
        }

        $properties = [];
        foreach ($rules as $field => $fieldRules) {
            if (str_contains($field, '.') || str_contains($field, '*')) {
                throw new InvalidArgumentException("Nested validation field [{$field}] is not supported. Define its object or array schema directly.");
            }
            if (isset($overrides[$field])) {
                $properties[$field] = $overrides[$field];

                continue;
            }
            [$parsedRules, $description] = $this->normalizeRules($field, $fieldRules);
            $type = $this->inferType($field, $parsedRules);
            $this->applyConstraints($field, $type, $parsedRules);
            if ($description !== null) {
                $type->description($description);
            }
            $properties[$field] = $type;
        }

        return $properties;
    }

    /**
     * @param  string|ValidationRule|Closure|list<string|ValidationRule|Closure>  $rules
     * @return array{list<ParsedRule>, string|null}
     */
    private function normalizeRules(string $field, string|ValidationRule|Closure|array $rules): array
    {
        $rules = is_string($rules) ? explode('|', $rules) : (is_array($rules) ? $rules : [$rules]);
        $parsed = [];
        $description = null;
        foreach ($rules as $rule) {
            if ($rule instanceof TitleAttribute) {
                $description = $rule->title;

                continue;
            }
            if (! is_string($rule)) {
                throw new InvalidArgumentException('Rule ['.get_debug_type($rule)."] on field [{$field}] requires an explicit schema override.");
            }

            /** @var array{string, list<string|null>} $parts */
            $parts = ValidationRuleParser::parse($rule);
            [$name, $parameters] = $parts;
            if (! in_array($name, [
                'String', 'Integer', 'Numeric', 'Boolean', 'Email', 'Date',
                'Required', 'Present', 'Sometimes', 'Nullable',
                'Min', 'Max', 'Between', 'Exists', 'Unique', 'Bail',
            ], true)) {
                throw new InvalidArgumentException("Rule [{$name}] on field [{$field}] requires an explicit schema override.");
            }
            $parsed[] = ['name' => $name, 'parameters' => $parameters];
        }

        return [$parsed, $description];
    }

    /** @param list<ParsedRule> $rules */
    private function inferType(string $field, array $rules): Types\Type
    {
        $names = array_column($rules, 'name');
        $types = [];
        foreach ($names as $name) {
            $type = match ($name) {
                'Integer' => 'integer',
                'Numeric' => 'number',
                'Boolean' => 'boolean',
                'String', 'Email', 'Date' => 'string',
                default => null,
            };
            if ($type !== null) {
                $types[$type] = true;
            }
        }
        // An integer also satisfies numeric validation; prefer the narrower type.
        if (isset($types['integer'])) {
            unset($types['number']);
        }
        if (count($types) !== 1) {
            throw new InvalidArgumentException("Field [{$field}] needs an unambiguous type rule or an explicit schema override.");
        }

        return match (array_key_first($types)) {
            'integer' => $this->schema->integer(),
            'number' => $this->schema->number(),
            'boolean' => $this->schema->boolean(),
            default => $this->schema->string(),
        };
    }

    /** @param list<ParsedRule> $rules */
    private function applyConstraints(string $field, Types\Type $type, array $rules): void
    {
        $names = array_column($rules, 'name');
        $required = in_array('Required', $names, true);
        $type->required(! in_array('Sometimes', $names, true) && ($required || in_array('Present', $names, true)));
        // Laravel's required rule rejects null even when nullable is also present.
        $type->nullable(in_array('Nullable', $names, true) && ! $required);
        $minimum = $required && $type instanceof Types\StringType ? 1.0 : null;
        $maximum = null;

        foreach ($rules as $rule) {
            $name = $rule['name'];
            if ($name === 'Email' && $type instanceof Types\StringType) {
                $type->format('email');
            }
            // Date, Exists and Unique remain runtime checks. Laravel's date rule
            // accepts more representations than JSON Schema's date/date-time formats.
            if (! in_array($name, ['Min', 'Max', 'Between'], true)) {
                continue;
            }
            $expected = $name === 'Between' ? 2 : 1;
            if (count($rule['parameters']) !== $expected) {
                throw new InvalidArgumentException("Rule [{$name}] on field [{$field}] expects {$expected} numeric parameters.");
            }
            $values = [];
            foreach ($rule['parameters'] as $parameter) {
                if (! is_numeric($parameter) || ! is_finite((float) $parameter)) {
                    throw new InvalidArgumentException("Rule [{$name}] on field [{$field}] requires finite numeric parameters.");
                }
                $integer = filter_var($parameter, FILTER_VALIDATE_INT);
                $values[] = $integer !== false ? $integer : (float) $parameter;
            }
            if ($name !== 'Max') {
                $minimum = $minimum === null ? $values[0] : max($minimum, $values[0]);
            }
            if ($name !== 'Min') {
                $upper = $values[$name === 'Between' ? 1 : 0];
                $maximum = $maximum === null ? $upper : min($maximum, $upper);
            }
        }

        if ($type instanceof Types\NumberType) {
            if ($minimum !== null) {
                $type->min($minimum);
            }
            if ($maximum !== null) {
                $type->max($maximum);
            }
        } elseif ($type instanceof Types\IntegerType || $type instanceof Types\StringType) {
            if ($minimum !== null) {
                $bound = $this->integerBound($field, $minimum, roundUp: true);
                $type->min($type instanceof Types\StringType ? max(0, $bound) : $bound);
            }
            if ($maximum !== null) {
                if ($type instanceof Types\StringType && $maximum < 0) {
                    throw new InvalidArgumentException("Field [{$field}] cannot have a negative maximum string length.");
                }
                $type->max($this->integerBound($field, $maximum, roundUp: false));
            }
        } elseif ($minimum !== null || $maximum !== null) {
            throw new InvalidArgumentException("Size rules on field [{$field}] require a numeric or string type, or an explicit schema override.");
        }
    }

    private function integerBound(string $field, int|float $value, bool $roundUp): int
    {
        if (is_int($value)) {
            return $value;
        }
        $rounded = $roundUp ? ceil($value) : floor($value);
        if ($rounded <= PHP_INT_MIN || $rounded >= PHP_INT_MAX) {
            throw new InvalidArgumentException("Size bound on field [{$field}] exceeds the supported integer range. Use an explicit schema override.");
        }

        return (int) $rounded;
    }
}
