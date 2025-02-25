<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Infrastructure\Symfony;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

final class NotifyBrowserAboutLogin
{
    private ?string $authenticatedUserId = null;

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $this->authenticatedUserId = $event->getAuthenticationToken()->getUserIdentifier();
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if ($this->authenticatedUserId === null || !$event->getRequest()->headers->has('Pe-Request')) {
            return;
        }

        $event->getResponse()->headers->set(
            'Pe-Dispatch',
            (string)json_encode(
                [['name' => 'WebInterface.UserArrived', 'detail' => ['userId' => $this->authenticatedUserId]]]
            )
        );

        // Prevent automatic redirection from being followed by window.fetch(),
        // allowing Pe-Dispatch to be processed before the redirection is manually followed.
        if ($event->getResponse()->isRedirection()) {
            $event->getResponse()->setStatusCode(Response::HTTP_OK);
            $event->getResponse()->headers->set('Pe-Location', $event->getResponse()->headers->get('Location'));
            $event->getResponse()->headers->remove('Location');
        }
    }
}
