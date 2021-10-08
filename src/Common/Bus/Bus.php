<?php

declare(strict_types=1);

namespace Gaming\Common\Bus;

use Exception;
use Gaming\Common\Bus\Exception\ApplicationException;
use Gaming\Common\Bus\Exception\MissingHandlerException;

interface Bus
{
    /**
     * Handle the given message.
     *
     * @param object $message
     *
     * @return mixed
     * @throws ApplicationException
     * @throws MissingHandlerException
     * @throws Exception Any application based exception
     */
    public function handle(object $message);
}
