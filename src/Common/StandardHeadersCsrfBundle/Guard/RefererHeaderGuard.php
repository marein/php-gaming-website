<?php
declare(strict_types=1);

namespace Gaming\Common\StandardHeadersCsrfBundle\Guard;

use Symfony\Component\HttpFoundation\Request;

final class RefererHeaderGuard implements Guard
{
    /**
     * @var string[]
     */
    private array $allowedOrigins;

    /**
     * RefererHeaderGuard constructor.
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
        return in_array(
            $this->readRefererSchemeAndHttpHostFromRequest($request),
            [...$this->allowedOrigins, $request->getSchemeAndHttpHost()],
            true
        );
    }

    /**
     * Returns the referer header in the same format as the origin header.
     *
     * @param Request $request
     *
     * @return string
     */
    private function readRefererSchemeAndHttpHostFromRequest(Request $request): string
    {
        $components = parse_url($request->headers->get('referer', ''));

        $referer = ($components['scheme'] ?? '') . '://' . ($components['host'] ?? '');

        if (array_key_exists('port', $components)) {
            $referer .= ':' . $components['port'];
        }

        return $referer;
    }
}
