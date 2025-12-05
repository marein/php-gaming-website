<?php

declare(strict_types=1);

namespace Gaming\Common\Bus;

use Exception;
use Gaming\Common\Bus\Exception\BusException;

interface Bus
{
    /**
     * @template TResponse
     *
     * @param Request<TResponse> $request
     *
     * @return TResponse
     * @throws BusException
     * @throws Exception Any application based exception
     */
    public function handle(Request $request): mixed;
}
