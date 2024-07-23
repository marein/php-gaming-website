<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Infrastructure\Security;

use Symfony\Bundle\SecurityBundle\Security as SymfonySecurity;
use Symfony\Component\Uid\NilUuid;

final class Security
{
    public function __construct(
        private readonly SymfonySecurity $security
    ) {
    }

    public function getUser(): User
    {
        if ($this->security->getUser() instanceof User) {
            return $this->security->getUser();
        }

        return new User((new NilUuid())->toRfc4122());
    }
}
