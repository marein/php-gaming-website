<?php
declare(strict_types=1);

namespace Gaming\Common\CsrfProtectionBundle\Guard;

use Symfony\Component\HttpFoundation\Request;

final class AtLeastOneGuard implements Guard
{
    /**
     * @var Guard[]
     */
    private array $guards;

    /**
     * AtLeastOneGuard constructor.
     *
     * @param Guard[] $guards
     */
    public function __construct(array $guards)
    {
        $this->guards = $guards;
    }

    /**
     * @inheritdoc
     */
    public function isSafe(Request $request): bool
    {
        foreach ($this->guards as $guard) {
            if ($guard->isSafe($request)) {
                return true;
            }
        }

        return false;
    }
}
