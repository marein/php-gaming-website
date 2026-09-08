<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Infrastructure\Symfony;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class HandleFragmentExceptions
{
    public function __construct(
        private readonly string $pathInfoPattern,
        private readonly int $minDelay = 3000,
        private readonly int $maxDelay = 5000
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (!preg_match($this->pathInfoPattern, $event->getRequest()->getPathInfo())) {
            return;
        }

        $event->setResponse(
            new Response(
                '<div hx-get="' . $event->getRequest()->getRequestUri() . '"
                      hx-trigger="load delay:' . rand($this->minDelay, $this->maxDelay) . 'ms"
                      hx-swap="outerHTML"
                      hx-sync="">
                    <span class="spinner-border spinner-border-sm" role="status"></span>
                </div>',
                match (true) {
                    $event->getThrowable() instanceof HttpException => $event->getThrowable()->getStatusCode(),
                    default => Response::HTTP_INTERNAL_SERVER_ERROR
                }
            )
        );
    }
}
