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
        if ($this->authenticatedUserId === null || !$event->getRequest()->isXmlHttpRequest()) {
            return;
        }

        $event->getResponse()->headers->set(
            'HX-Trigger',
            (string)json_encode(
                ['WebInterface.UserArrived' => ['userId' => $this->authenticatedUserId]]
            )
        );

        if ($event->getRequest()->headers->has('HX-Request') && $event->getResponse()->isRedirection()) {
            $event->getResponse()->setStatusCode(Response::HTTP_OK);
            $event->getResponse()->headers->set('HX-Location', $event->getResponse()->headers->get('Location'));
            $event->getResponse()->headers->remove('Location');
        }
    }
}
