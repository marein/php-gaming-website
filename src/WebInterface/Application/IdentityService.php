<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Application;

use Gaming\Common\Bus\Exception\ApplicationException;

interface IdentityService
{
    /**
     * @return array<string, mixed>
     */
    public function arrive(): array;

    /**
     * @return array<string, mixed>
     * @throws ApplicationException
     */
    public function signUp(string $userId, string $email, string $username, bool $dryRun = false): array;
}
