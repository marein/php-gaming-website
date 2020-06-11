<?php
declare(strict_types=1);

namespace Gaming\Common\StandardHeadersCsrfBundle\Guard;

use Symfony\Component\HttpFoundation\Request;

final class OriginHeaderGuard implements Guard
{
    /**
     * @var string[]
     */
    private array $allowedOrigins;

    /**
     * OriginHeaderGuard constructor.
     *
     * @param string[] $allowedOrigins
     */
    public function __construct(array $allowedOrigins)
    {
        $this->allowedOrigins = $allowedOrigins;
    }

    /**
     * @inheritdoc
     */
    public function isSafe(Request $request): bool
    {
        $origin = $request->headers->get('origin', '');

        return in_array(
            $origin,
            [...$this->allowedOrigins, $request->getSchemeAndHttpHost()],
            true
        );
    }
}
