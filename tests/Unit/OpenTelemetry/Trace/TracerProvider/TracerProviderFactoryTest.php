<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Trace\TracerProvider;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor\NoopSpanProcessorFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TracerProvider\DefaultTracerProviderFactory;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TracerProvider\DefaultTracerProviderFactory
 */
class TracerProviderFactoryTest extends TestCase
{
    public function testCreateProvider(): void
    {
        (new DefaultTracerProviderFactory())->createProvider(processors: [(new NoopSpanProcessorFactory())->createProcessor()]);

        self::expectExceptionObject(new \InvalidArgumentException('Processors should not be empty'));

        (new DefaultTracerProviderFactory())->createProvider();
    }
}
