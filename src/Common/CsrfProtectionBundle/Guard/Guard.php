<?php
declare(strict_types=1);

namespace Gaming\Common\CsrfProtectionBundle\Guard;

use Symfony\Component\HttpFoundation\Request;

interface Guard
{
    /**
     * Returns true if the request is safe.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function isSafe(Request $request): bool;
}
