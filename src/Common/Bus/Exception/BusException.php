<?php

declare(strict_types=1);

namespace Gaming\Common\Bus\Exception;

use Exception;

class BusException extends Exception
{
    public static function missingHandler(string $requestClass): self
    {
        return new self(sprintf('Missing handler for "%s".', $requestClass));
    }
}
