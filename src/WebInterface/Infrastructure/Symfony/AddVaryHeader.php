<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Infrastructure\Symfony;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

final class AddVaryHeader
{
    /**
     * @param string[] $headers
     */
    public function __construct(
        private readonly array $headers
    ) {
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $event->getResponse()->setVary($this->headers, false);
    }
}
