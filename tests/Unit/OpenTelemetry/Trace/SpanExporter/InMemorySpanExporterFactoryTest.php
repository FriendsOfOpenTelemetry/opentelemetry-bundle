<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Trace\SpanExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\EmptyExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\InMemorySpanExporterFactory;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\InMemorySpanExporterFactory
 */
class InMemorySpanExporterFactoryTest extends TestCase
{
    /**
     * @dataProvider exporterProvider
     */
    public function testCreateExporter(string $dsn, ExporterOptionsInterface $options, ?\Exception $exception): void
    {
        if (null !== $exception) {
            self::expectExceptionObject($exception);
        }

        $this->expectNotToPerformAssertions();

        InMemorySpanExporterFactory::createExporter(ExporterDsn::fromString($dsn), $options);
    }

    /**
     * @return \Generator<array{0: string, 1: ExporterOptionsInterface, 2: ?\Exception}>
     */
    public function exporterProvider(): \Generator
    {
        yield [
            'in-memory://default',
            new EmptyExporterOptions(),
            null,
        ];
    }
}
