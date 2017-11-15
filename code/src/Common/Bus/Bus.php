<?php

namespace Gambling\Common\Bus;

use Gambling\Common\Bus\Exception\BusException;

interface Bus
{
    /**
     * Handle the given command.
     *
     * @param mixed $command
     *
     * @return mixed
     * @throws BusException Must be thrown if something went wrong.
     */
    public function handle($command);
}
