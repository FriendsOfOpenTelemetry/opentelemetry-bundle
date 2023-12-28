<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Trace\SpanProcessor;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\InMemorySpanExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor\SimpleSpanProcessorFactory;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor\SimpleSpanProcessorFactory
 */
class SimpleSpanProcessorFactoryTest extends TestCase
{
    public function testCreateProcessor(): void
    {
        SimpleSpanProcessorFactory::createProcessor(exporter: InMemorySpanExporterFactory::createExporter());

        self::expectExceptionObject(new \InvalidArgumentException('Exporter is null'));

        SimpleSpanProcessorFactory::createProcessor();
    }
}
