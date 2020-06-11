<?php
declare(strict_types=1);

namespace Gaming\Common\StandardHeadersCsrfBundle\Guard;

use Symfony\Component\HttpFoundation\Request;

final class FeatureToggleGuard implements Guard
{
    /**
     * @var bool
     */
    private bool $isEnabled;

    /**
     * @var Guard
     */
    private Guard $guard;

    /**
     * FeatureToggleGuard constructor.
     *
     * @param bool  $isEnabled
     * @param Guard $guard
     */
    public function __construct(bool $isEnabled, Guard $guard)
    {
        $this->isEnabled = $isEnabled;
        $this->guard = $guard;
    }

    /**
     * @inheritdoc
     */
    public function isSafe(Request $request): bool
    {
        return $this->isEnabled && $this->guard->isSafe($request);
    }
}
