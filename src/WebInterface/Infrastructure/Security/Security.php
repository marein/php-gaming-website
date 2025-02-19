<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Infrastructure\Security;

use Gaming\Common\Bus\Bus;
use Gaming\Identity\Application\User\Command\ArriveCommand;
use Symfony\Bundle\SecurityBundle\Security as SymfonySecurity;

final class Security
{
    public function __construct(
        private readonly SymfonySecurity $security,
        private readonly Bus $identityCommandBus
    ) {
    }

    public function forceUser(): User
    {
        if ($this->security->getUser() instanceof User) {
            return $this->security->getUser();
        }

        $this->security->login(
            $user = new User($this->identityCommandBus->handle(new ArriveCommand()))
        );

        return $user;
    }
}
