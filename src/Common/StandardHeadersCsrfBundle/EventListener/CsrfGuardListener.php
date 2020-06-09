<?php
declare(strict_types=1);

namespace Gaming\Common\StandardHeadersCsrfBundle\EventListener;

use Gaming\Common\StandardHeadersCsrfBundle\Guard\Guard;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class CsrfGuardListener
{
    /**
     * @var Guard
     */
    private Guard $guard;

    /**
     * CsrfGuardListener constructor.
     *
     * @param Guard $guard
     */
    public function __construct(Guard $guard)
    {
        $this->guard = $guard;
    }

    /**
     * Protect against CSRF attacks with the help of standard headers.
     *
     * @param RequestEvent $event
     *
     * @throws AccessDeniedHttpException When a CSRF attack is detected.
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if ($event->isMasterRequest() && !$this->guard->isSafe($event->getRequest())) {
            throw new AccessDeniedHttpException('CSRF attack detected.');
        }
    }
}
