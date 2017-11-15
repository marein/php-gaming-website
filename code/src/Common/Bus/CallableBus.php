<?php

namespace Gambling\Common\Bus;

use Gambling\Common\Bus\Exception\CommandHasNoHandlerException;

final class CallableBus implements Bus
{
    /**
     * @var array
     */
    private $handler;

    /**
     * CallableBus constructor.
     */
    public function __construct()
    {
        $this->handler = [];
    }

    /**
     * Add a handler.
     *
     * @param string   $commandClass
     * @param callable $handler
     */
    public function addHandler(string $commandClass, callable $handler): void
    {
        $this->handler[$commandClass] = $handler;
    }

    /**
     * @inheritdoc
     */
    public function handle($command)
    {
        $class = get_class($command);

        if (!isset($this->handler[$class])) {
            throw new CommandHasNoHandlerException(sprintf('Given "%s"', $class));
        }

        return $this->handler[$class]($command);
    }
}
