<?php

declare(strict_types=1);

namespace App\Domains\OAuth\CompleteOAuthDemo;

use App\Domains\OAuth\StartOAuthDemo\OAuthDemoSession;
use App\Domains\Shared\UseCaseInput;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** @extends UseCaseInput<array{session: OAuthDemoSession|null, state?: string|null, code?: string|null, authorization_error?: string|null}> */
final readonly class CompleteOAuthDemoUseCaseInput extends UseCaseInput
{
    public function __construct(
        public ?OAuthDemoSession $session,
        public ?string $state,
        public ?string $code,
        public ?string $authorizationError = null,
    ) {}

    /** @return array<string, list<string|ValidationRule|Closure>> */
    public static function rules(): array
    {
        return [
            'session' => ['present', 'nullable', static function (string $attribute, mixed $value, Closure $fail): void {
                if (! $value instanceof OAuthDemoSession) {
                    $fail('The :attribute field must contain an OAuth demo session.');
                }
            }],
            'state' => ['nullable', 'string'],
            'code' => ['nullable', 'string'],
            'authorization_error' => ['nullable', 'string'],
        ];
    }

    /** @param array{session: OAuthDemoSession|null, state?: string|null, code?: string|null, authorization_error?: string|null} $data */
    protected static function fromValidatedArray(array $data): static
    {
        return new self(
            session: $data['session'],
            state: $data['state'] ?? null,
            code: $data['code'] ?? null,
            authorizationError: $data['authorization_error'] ?? null,
        );
    }
}
