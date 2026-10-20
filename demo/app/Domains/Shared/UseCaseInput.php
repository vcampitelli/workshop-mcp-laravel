<?php

declare(strict_types=1);

namespace App\Domains\Shared;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/** @template TData of array<string, mixed> */
abstract readonly class UseCaseInput
{
    /**
     * @param  array<array-key, mixed>  $data
     *
     * @throws ValidationException
     */
    final public static function fromArray(array $data): static
    {
        /** @var TData $validated */
        $validated = Validator::make($data, static::rules())->validate();

        return static::fromValidatedArray($validated);
    }

    /** @return array<string, list<string|ValidationRule|Closure>> */
    abstract public static function rules(): array;

    /** @param TData $data */
    abstract protected static function fromValidatedArray(array $data): static;
}
