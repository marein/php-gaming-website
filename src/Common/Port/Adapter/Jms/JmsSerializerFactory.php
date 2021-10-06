<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Jms;

use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;

/**
 * This class provides a factory for jms/serializer used in our service container.
 * The bundle which integrates jms/serializer with symfony cleanly is currently only
 * capable of configuring one serializer for the application. This factory can be used
 * to create a jms/serializer for each context with some application-wide defaults.
 */
final class JmsSerializerFactory
{
    /**
     * @param array<string, string> $metadataDirectories
     * @param iterable<SubscribingHandlerInterface> $subscribingHandlers
     */
    public static function create(
        bool $enableDebugMode,
        string $cacheDirectory,
        array $metadataDirectories,
        iterable $subscribingHandlers
    ): Serializer {
        return SerializerBuilder::create()
            ->setDebug($enableDebugMode)
            ->setCacheDir($cacheDirectory)
            ->addMetadataDirs($metadataDirectories)
            ->setPropertyNamingStrategy(
                new IdenticalPropertyNamingStrategy()
            )
            ->includeInterfaceMetadata(true)
            ->setDocBlockTypeResolver(true)
            ->configureHandlers(
                static function (HandlerRegistry $registry) use ($subscribingHandlers) {
                    foreach ($subscribingHandlers as $subscribingHandler) {
                        $registry->registerSubscribingHandler($subscribingHandler);
                    }
                }
            )
            ->configureListeners(
                static fn() => true
            )
            ->setSerializationContextFactory(
                static fn() => SerializationContext::create()
                    ->setSerializeNull(true)
            )
            ->build();
    }
}
