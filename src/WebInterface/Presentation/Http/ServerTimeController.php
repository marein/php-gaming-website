<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Presentation\Http;

use Symfony\Component\HttpFoundation\JsonResponse;

final class ServerTimeController
{
    public function serverTimeAction(): JsonResponse
    {
        return new JsonResponse((int)(microtime(true) * 1000));
    }
}
