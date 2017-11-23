<?php

namespace Gambling\Common\Application;

final class InvokeApplicationLifeCycle implements ApplicationLifeCycle
{
    /**
     * @inheritdoc
     */
    public function run(callable $action)
    {
        return $action();
    }
}
