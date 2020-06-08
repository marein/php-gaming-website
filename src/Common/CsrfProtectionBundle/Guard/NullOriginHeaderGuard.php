<?php
declare(strict_types=1);

namespace Gaming\Common\CsrfProtectionBundle\Guard;

use Symfony\Component\HttpFoundation\Request;

final class NullOriginHeaderGuard implements Guard
{
    /**
     * @var bool
     */
    private bool $isEnabled;

    /**
     * NullOriginHeaderGuard constructor.
     *
     * @param bool $isEnabled
     */
    public function __construct(bool $isEnabled)
    {
        $this->isEnabled = $isEnabled;
    }

    /**
     * @inheritdoc
     */
    public function isSafe(Request $request): bool
    {
        return $this->isEnabled && $request->headers->get('origin', '') === 'null';
    }
}
