<?php

namespace Gambling\WebInterface\Infrastructure\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

final class ExceptionToJsonListener
{
    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        $request = $event->getRequest();

        if (!$event->isMasterRequest() || $request->getRequestFormat() !== 'json') {
            return;
        }

        // todo: Return meaningful exception messages for the user.

        // Transform exception class as message from "FooBarException" to "Foo Bar Exception".
        $reflectionClass = new \ReflectionClass(
            $event->getException()
        );

        $partsOfClassName = preg_split(
            '/(?=[A-Z])/',
            $reflectionClass->getShortName()
        );

        $message = implode(' ', $partsOfClassName);

        $event->setResponse(
            new JsonResponse([
                'message' => $message
            ])
        );
    }
}
