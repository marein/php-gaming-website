<?php

declare(strict_types=1);

namespace Gaming\Identity\Port\Adapter\Validation;

use Attribute;
use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

#[Attribute]
final class EmailRequirements extends Compound
{
    protected function getConstraints(array $options): array
    {
        return [
            new NotBlank(),
            new Email(),
            new Length(max: 255)
        ];
    }
}
