<?php

declare(strict_types=1);

namespace Gaming\Common\Bus;

use Gaming\Common\Bus\Exception\MissingHandlerException;

final class RoutingBus implements Bus
{
    /**
     * @var callable[]
     */
    private array $messageClassToHandlerMap;

    /**
     * RoutingBus constructor.
     *
     * @param callable[] $messageClassToHandlerMap
     */
    public function __construct(array $messageClassToHandlerMap)
    {
        $this->messageClassToHandlerMap = $messageClassToHandlerMap;
    }

    /**
     * @inheritdoc
     */
    public function handle(object $message)
    {
        $messageClass = get_class($message);

        if (!isset($this->messageClassToHandlerMap[$messageClass])) {
            throw new MissingHandlerException(sprintf('Given "%s"', $messageClass));
        }

        return $this->messageClassToHandlerMap[$messageClass]($message);
    }
}
