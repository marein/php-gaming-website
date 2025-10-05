<?php

declare(strict_types=1);

namespace Gaming\Identity\Port\Adapter\Validation;

use Gaming\Identity\Domain\Model\User\UsernameGenerator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class ReservedUsernamesValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint)
    {
        if (!$constraint instanceof ReservedUsernames) {
            throw new UnexpectedTypeException($constraint, ReservedUsernames::class);
        }

        $normalizedValue = strtolower($value);

        foreach (UsernameGenerator::USERNAME_VERBS as $verb) {
            foreach (UsernameGenerator::USERNAME_NOUNS as $noun) {
                if ($normalizedValue === strtolower($verb . $noun)) {
                    $this->context->buildViolation($constraint->message)
                        ->setParameter('{{ username }}', $value)
                        ->addViolation();

                    return;
                }
            }
        }
    }
}
