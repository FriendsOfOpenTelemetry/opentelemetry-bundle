<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Trace\TracerProvider;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor\NoopSpanProcessorFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TracerProvider\TracerProviderFactory;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TracerProvider\TracerProviderFactory
 */
class TracerProviderFactoryTest extends TestCase
{
    public function testCreateProvider(): void
    {
        TracerProviderFactory::createProvider(processors: [NoopSpanProcessorFactory::createProcessor()]);

        self::expectExceptionObject(new \InvalidArgumentException('Processors should not be empty'));

        TracerProviderFactory::createProvider();
    }
}
