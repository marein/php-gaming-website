<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Infrastructure\Security;

use Gaming\Common\Bus\Bus;
use Gaming\Identity\Application\User\Command\ArriveCommand;
use Symfony\Bundle\SecurityBundle\Security as SymfonySecurity;
use Symfony\Component\Uid\NilUuid;

final class Security
{
    public function __construct(
        private readonly SymfonySecurity $security,
        private readonly Bus $identityCommandBus
    ) {
    }

    public function tryUser(): User
    {
        $user = $this->security->getUser();

        return match (true) {
            $user instanceof User => $user,
            default => new User((new NilUuid())->toRfc4122())
        };
    }

    public function getUser(): User
    {
        $user = $this->tryUser();
        if ((new NilUuid())->toRfc4122() !== $user->getUserIdentifier()) {
            return $user;
        }

        $this->security->login(
            $user = new User($this->identityCommandBus->handle(new ArriveCommand()))
        );

        return $user;
    }
}
