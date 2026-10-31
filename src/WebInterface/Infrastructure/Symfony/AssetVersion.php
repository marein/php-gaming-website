<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Infrastructure\Symfony;

use Symfony\Component\AssetMapper\ImportMap\ImportMapRenderer;

final class AssetVersion
{
    private ?string $version = null;

    public function __construct(
        private readonly ImportMapRenderer $importMapRenderer,
        private readonly string $entrypoint
    ) {
    }

    public function current(): string
    {
        return $this->version ??= hash('xxh128', $this->importMapRenderer->render($this->entrypoint));
    }
}
