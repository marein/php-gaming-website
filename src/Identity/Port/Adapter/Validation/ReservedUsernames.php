<?php

declare(strict_types=1);

namespace Gaming\Identity\Port\Adapter\Validation;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
final class ReservedUsernames extends Constraint
{
    public string $message = 'The username "{{ username }}" is reserved.';

    public function validatedBy(): string
    {
        return self::class . 'Validator';
    }
}
