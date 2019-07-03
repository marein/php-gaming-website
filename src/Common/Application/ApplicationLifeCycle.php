<?php
declare(strict_types=1);

namespace Gaming\Common\Application;

interface ApplicationLifeCycle
{
    /**
     * Run the given action.
     *
     * @param callable $action
     *
     * @return mixed
     * @throws \Exception Any application based exception
     */
    public function run(callable $action);
}
