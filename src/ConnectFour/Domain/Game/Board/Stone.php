<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Board;

enum Stone: int
{
    case None = 0;
    case Red = 1;
    case Yellow = 2;

    /**
     * @deprecated Backward compatibility.
     */
    public static function yellow(): Stone
    {
        return Stone::Yellow;
    }

    /**
     * @deprecated Backward compatibility.
     */
    public function color(): int
    {
        return $this->value;
    }
}
