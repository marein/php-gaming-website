<?php
declare(strict_types=1);

namespace Gaming\Common\StandardHeadersCsrfBundle\Guard;

use Symfony\Component\HttpFoundation\Request;

final class NullOriginHeaderGuard implements Guard
{
    /**
     * @inheritdoc
     */
    public function isSafe(Request $request): bool
    {
        return $request->headers->get('origin', '') === 'null';
    }
}
