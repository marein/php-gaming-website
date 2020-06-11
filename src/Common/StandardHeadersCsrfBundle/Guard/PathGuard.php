<?php
declare(strict_types=1);

namespace Gaming\Common\StandardHeadersCsrfBundle\Guard;

use Symfony\Component\HttpFoundation\Request;

final class PathGuard implements Guard
{
    /**
     * @var string[]
     */
    private array $patterns;

    /**
     * PathGuard constructor.
     *
     * @param string[] $patterns
     */
    public function __construct(array $patterns)
    {
        $this->patterns = $patterns;
    }

    /**
     * @inheritdoc
     */
    public function isSafe(Request $request): bool
    {
        foreach ($this->patterns as $pattern) {
            if (preg_match('#' . $pattern . '#', $request->getPathInfo())) {
                return false;
            }
        }

        return true;
    }
}
