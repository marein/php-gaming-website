<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Infrastructure\EventListener;

use Gaming\WebInterface\Application\IdentityService;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class AssignUserIdOnKernelRequest
{
    public function __construct(
        private readonly IdentityService $identityService,
        private readonly string $ignoredPathExpression
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($this->shouldIgnoreRequest($event)) {
            return;
        }

        $session = $event->getRequest()->getSession();

        if (!$session->has('user')) {
            // todo Create user only when it's really needed.
            // Currently, a new user is created in the identity context for each visitor of this website.
            // In the future, a new user must only be created when it performs an action where an identity is necessary.
            // For example: open a game, sign up etc.
            $session->set('user', $this->identityService->arrive()['userId']);
        }
    }

    private function shouldIgnoreRequest(RequestEvent $event): bool
    {
        return !$event->isMainRequest()
            || preg_match($this->ignoredPathExpression, $event->getRequest()->getRequestUri()) === 1;
    }
}
