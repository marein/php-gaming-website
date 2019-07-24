<?php
declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Symfony;

use Exception;
use ReflectionClass;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Handles the exception for the whole gaming domain based on convention.
 * For example, if an exception has "NotFound" in its name, this class sets the status code to 404.
 *
 * todo: Return meaningful exception messages for the user.
 */
final class GamingExceptionListener
{
    /**
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        if ($event->getRequest()->getRequestFormat() === 'json') {
            $this->handleJson($event);
        } else {
            $this->handleOther($event);
        }
    }

    /**
     * Set a new json response.
     *
     * @param ExceptionEvent $event
     */
    private function handleJson(ExceptionEvent $event): void
    {
        $exceptionName = $this->exceptionName($event->getException());

        $event->setResponse(
            new JsonResponse(
                [
                    'message' => $exceptionName
                ],
                $this->statusCodeByExceptionName($exceptionName)
            )
        );
    }

    /**
     * Set a new exception when 404 is detected.
     *
     * @param ExceptionEvent $event
     */
    private function handleOther(ExceptionEvent $event): void
    {
        $exceptionName = $this->exceptionName($event->getException());

        if ($this->statusCodeByExceptionName($exceptionName) === 404) {
            $event->setException(
                new NotFoundHttpException(
                    $exceptionName,
                    $event->getException()
                )
            );
        }
    }

    /**
     * Returns the status code based on exception name.
     *
     * @param string $exceptionName
     *
     * @return int
     */
    private function statusCodeByExceptionName(string $exceptionName): int
    {
        return strpos(
            $exceptionName,
            'Not Found'
        ) !== false ? Response::HTTP_NOT_FOUND : Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    /**
     * Returns the name of the exception without namespace and trailing "Exception".
     *
     * @param Exception $exception
     *
     * @return string
     */
    private function exceptionName(Exception $exception): string
    {
        $exceptionName = str_replace(
            'Exception',
            '',
            (new ReflectionClass($exception))->getShortName()
        );

        $exceptionWords = preg_split(
            '/(?=[A-Z])/',
            $exceptionName
        );

        return implode(' ', $exceptionWords);
    }
}
