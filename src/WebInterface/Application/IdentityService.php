<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Application;

interface IdentityService
{
    /**
     * @return array<string, mixed>
     */
    public function arrive(): array;

    /**
     * @return array<string, mixed>
     */
    public function signUp(string $userId, string $email, string $username): array;
}
