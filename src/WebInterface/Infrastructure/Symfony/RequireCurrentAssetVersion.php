<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Infrastructure\Symfony;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class RequireCurrentAssetVersion
{
    public function __construct(
        private readonly AssetVersion $assetVersion
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $headers = $event->getRequest()->headers;

        if (!$event->getRequest()->isMethodSafe() || !$headers->has('X-Asset-Version')) {
            return;
        }

        $current = $this->assetVersion->current();

        if ($headers->get('X-Asset-Version') === $current) {
            return;
        }

        $event->setResponse(
            new Response('', Response::HTTP_CONFLICT, ['X-Asset-Version' => $current])
        );
    }
}
