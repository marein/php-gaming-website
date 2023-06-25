<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore\Integration\Doctrine;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

final class SkipDebugMatchingSqlPatternLogger extends AbstractLogger
{
    /**
     * @param string[] $skippableSqlPatterns
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly array $skippableSqlPatterns
    ) {
    }

    /**
     * @param mixed[] $context
     */
    public function debug(\Stringable|string $message, array $context = []): void
    {
        foreach ($this->skippableSqlPatterns as $skippableSqlPattern) {
            if (preg_match($skippableSqlPattern, $context['sql'] ?? '')) {
                return;
            }
        }

        parent::debug($message, $context);
    }

    /**
     * @param mixed[] $context
     */
    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }
}
