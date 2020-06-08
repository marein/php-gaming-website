<?php
declare(strict_types=1);

namespace Gaming\Common\CsrfProtectionBundle\EventListener;

use Gaming\Common\CsrfProtectionBundle\Guard\AtLeastOneGuard;
use Gaming\Common\CsrfProtectionBundle\Guard\NullOriginHeaderGuard;
use Gaming\Common\CsrfProtectionBundle\Guard\OriginHeaderGuard;
use Gaming\Common\CsrfProtectionBundle\Guard\PathGuard;
use Gaming\Common\CsrfProtectionBundle\Guard\RefererHeaderGuard;
use Gaming\Common\CsrfProtectionBundle\Guard\SafeMethodGuard;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class CsrfProtectionListener
{
    /**
     * @var string[]
     */
    private array $protectedPaths;

    /**
     * @var string[]
     */
    private array $allowedOrigins;

    /**
     * @var bool
     */
    private bool $shouldFallbackToReferer;

    /**
     * @var bool
     */
    private bool $shouldAllowNullOrigin;

    /**
     * CsrfProtectionListener constructor.
     *
     * @param string[] $protectedPaths
     * @param string[] $allowedOrigins
     * @param bool     $shouldFallbackToReferer
     * @param bool     $shouldAllowNullOrigin
     */
    public function __construct(
        array $protectedPaths,
        array $allowedOrigins,
        bool $shouldFallbackToReferer,
        bool $shouldAllowNullOrigin
    ) {
        $this->protectedPaths = $protectedPaths;
        $this->allowedOrigins = $allowedOrigins;
        $this->shouldFallbackToReferer = $shouldFallbackToReferer;
        $this->shouldAllowNullOrigin = $shouldAllowNullOrigin;
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
        if (!$event->isMasterRequest()) {
            return;
        }

        $guard = new AtLeastOneGuard(
            [
                new SafeMethodGuard(),
                new PathGuard($this->protectedPaths),
                new OriginHeaderGuard($this->allowedOrigins),
                new RefererHeaderGuard($this->shouldFallbackToReferer, $this->allowedOrigins),
                new NullOriginHeaderGuard($this->shouldAllowNullOrigin)
            ]
        );

        if (!$guard->isSafe($event->getRequest())) {
            throw new AccessDeniedHttpException('CSRF attack detected.');
        }
    }
}
