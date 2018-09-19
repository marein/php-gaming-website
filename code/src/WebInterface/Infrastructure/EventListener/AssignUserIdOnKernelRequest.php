<?php
declare(strict_types=1);

namespace Gambling\WebInterface\Infrastructure\EventListener;

use Gambling\WebInterface\Application\IdentityService;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

final class AssignUserIdOnKernelRequest
{
    /**
     * @var IdentityService
     */
    private $identityService;

    /**
     * AssignUserIdOnKernelRequest constructor.
     *
     * @param IdentityService $identityService
     */
    public function __construct(IdentityService $identityService)
    {
        $this->identityService = $identityService;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $session = $request->getSession();

        if (!$session->has('user')) {
            // todo Create user only when it's really needed.
            // Currently, a new user is created in the identity context for each visitor of this website.
            // In the future, a new user must only be created when it performs an action where an identity is necessary.
            // For example: open a game, sign up etc.
            $session->set('user', $this->identityService->arrive()['userId']);
        }
    }
}
