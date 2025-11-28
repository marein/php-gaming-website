<?php

declare(strict_types=1);

namespace Gaming\Identity\Application\Account\Query\GetUsernames;

use Gaming\Common\Bus\Request;

/**
 * @implements Request<GetUsernamesResponse>
 */
final class GetUsernames implements Request
{
    /**
     * @param string[] $accountIds
     */
    public function __construct(
        public readonly array $accountIds
    ) {
    }
}
