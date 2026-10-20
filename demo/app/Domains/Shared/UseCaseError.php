<?php

declare(strict_types=1);

namespace App\Domains\Shared;

enum UseCaseError
{
    case NotFound;
    case InvalidInput;
}
