<?php

namespace Gambling\WebInterface\Infrastructure\EventListener;

use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

final class AssignUserIdOnKernelRequest
{
    public function onKernelRequest(GetResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $session = $request->getSession();

        if (!$session->has('user')) {
            $session->set('user', Uuid::uuid4()->toString());
        }
    }
}
