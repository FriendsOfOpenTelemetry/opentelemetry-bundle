<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Trace\SpanProcessor;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\EmptyExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\InMemorySpanExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor\SimpleSpanProcessorFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportFactory;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor\SimpleSpanProcessorFactory
 */
class SimpleSpanProcessorFactoryTest extends TestCase
{
    public function testCreateProcessor(): void
    {
        (new SimpleSpanProcessorFactory())->createProcessor(
            [],
            (new InMemorySpanExporterFactory(new TransportFactory([])))
                ->createExporter(
                    ExporterDsn::fromString('null://default'),
                    EmptyExporterOptions::fromConfiguration([]),
                ));

        self::expectExceptionObject(new \InvalidArgumentException('Exporter is null'));

        (new SimpleSpanProcessorFactory())->createProcessor();
    }
}
