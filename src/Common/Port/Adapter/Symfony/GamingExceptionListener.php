<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Symfony;

use ReflectionClass;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Handles the exception for the whole gaming domain based on convention.
 * For example, if an exception has "NotFound" in its name, this class sets the status code to 404.
 *
 * @deprecated See https://github.com/marein/php-gaming-website/issues/34
 */
final class GamingExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        if ($event->getRequest()->getRequestFormat() === 'json') {
            $this->handleJson($event);
        } else {
            $this->handleOther($event);
        }
    }

    private function handleJson(ExceptionEvent $event): void
    {
        $exceptionName = $this->exceptionName($event->getThrowable());

        $event->setResponse(
            new JsonResponse(
                [
                    'message' => $exceptionName
                ],
                $this->statusCodeByExceptionName($exceptionName)
            )
        );
    }

    private function handleOther(ExceptionEvent $event): void
    {
        $exceptionName = $this->exceptionName($event->getThrowable());

        if ($this->statusCodeByExceptionName($exceptionName) === 404) {
            $event->setThrowable(
                new NotFoundHttpException(
                    $exceptionName,
                    $event->getThrowable()
                )
            );
        }
    }

    private function statusCodeByExceptionName(string $exceptionName): int
    {
        return strpos(
            $exceptionName,
            'Not Found'
        ) !== false ? Response::HTTP_NOT_FOUND : Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    private function exceptionName(Throwable $throwable): string
    {
        $exceptionName = str_replace(
            'Exception',
            '',
            (new ReflectionClass($throwable))->getShortName()
        );

        $exceptionWords = (array)preg_split(
            '/(?=[A-Z])/',
            $exceptionName
        );

        return implode(' ', $exceptionWords);
    }
}
