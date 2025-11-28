<?php

declare(strict_types=1);

namespace Gaming\Identity\Port\Adapter\Validation;

use Attribute;
use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

#[Attribute]
final class UsernameRequirements extends Compound
{
    protected bool $withBotRestriction;

    public function __construct(mixed $options = null, ?array $groups = null, mixed $payload = null)
    {
        $this->withBotRestriction = !isset($options['withBotRestriction']) || (bool)$options['withBotRestriction'];

        parent::__construct($options, $groups, $payload);
    }

    protected function getConstraints(array $options): array
    {
        $constraints = [
            $this->withBotRestriction
                ? new Regex('/(b|8)+_*(o|0)+_*(t|7|4)+_*/i', 'The username {{ value }} is reserved.', match: false)
                : null,
            new Length(min: 3, max: 20),
            new ReservedUsernames(),
            new Regex(
                '/(g|6|9)+_*(u|v|μ|µ)+_*(e|3)+_*(s|5|z)+_*(t|7)+_*/i',
                'The username {{ value }} is reserved.',
                match: false,
            ),
            new Regex(
                '/^(?!_)(?!.*__)[a-zA-Z0-9_]+(?<!_)+$/',
                'Use only letters, numbers and underscores (no leading, trailing, or consecutive underscores).'
            )
        ];

        return array_filter($constraints);
    }
}
