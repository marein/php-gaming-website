<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Infrastructure\Symfony;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

final class ReplaceSymfonyToolbar
{
    public function onKernelResponse(ResponseEvent $event): void
    {
        if ($event->getRequest()->isXmlHttpRequest()) {
            $event->getResponse()->headers->set('Symfony-Debug-Toolbar-Replace', '1');
        }
    }
}
