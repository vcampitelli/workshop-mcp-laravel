<?php

declare(strict_types=1);

namespace App\Mcp\Mappers;

use App\Rules\TitleAttribute;
use Laravel\Mcp\Server\Prompts\Argument;

class ValidationToArguments
{
    /**
     * @return list<Argument>
     */
    public function map(array $rules): array
    {
        $arguments = [];

        foreach ($rules as $field => $rule) {
            if (\is_string($rule)) {
                $rule = \explode('|', $rule);
            }

            // @TODO checar outros tipos de validação por classe
            if (!\is_array($rule)) {
                continue;
            }

            $arguments[] = $this->buildArgument($field, $rule);
        }

        return $arguments;
    }

    protected function buildArgument(string $field, array $rules): Argument
    {
        $description = '';
        $required = false;

        foreach ($rules as $rule) {
            if ($rule instanceof TitleAttribute) {
                $description = $rule->title;
                continue;
            }

            if ($rule === 'required') {
                $required = true;
            }
        }

        return new Argument(
            name: $field,
            description: $description,
            required: $required,
        );
    }
}
