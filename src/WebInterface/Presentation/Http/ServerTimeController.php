<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Presentation\Http;

use DateTimeImmutable;
use DateTimeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final class ServerTimeController
{
    public function serverTimeAction(): JsonResponse
    {
        return new JsonResponse(
            new DateTimeImmutable()->format(DateTimeInterface::ATOM)
        );
    }
}
