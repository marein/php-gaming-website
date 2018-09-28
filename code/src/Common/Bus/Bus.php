<?php
declare(strict_types=1);

namespace Gambling\Common\Bus;

interface Bus
{
    /**
     * Handle the given message.
     *
     * @param object $message
     *
     * @return mixed
     * @throws \Exception Any application based exception
     */
    public function handle(object $message);
}
