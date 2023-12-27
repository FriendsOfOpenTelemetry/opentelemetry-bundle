<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Metric\MetricExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\InMemoryMetricExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\MetricTemporalityEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporterOptions;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\InMemoryMetricExporterFactory
 */
class InMemoryMetricExporterFactoryTest extends TestCase
{
    /**
     * @dataProvider exporterProvider
     */
    public function testCreateExporter(string $dsn, ExporterOptionsInterface $options, ?string $temporality, ?\Exception $exception): void
    {
        if (null !== $exception) {
            self::expectExceptionObject($exception);
        }

        $exporter = InMemoryMetricExporterFactory::createExporter(ExporterDsn::fromString($dsn), $options);

        $reflection = new \ReflectionObject($exporter);
        $reflectedTemporality = $reflection->getProperty('temporality');

        self::assertSame($temporality, $reflectedTemporality->getValue($exporter));
    }

    /**
     * @return \Generator<array{
     *     0: string,
     *     1: ExporterOptionsInterface,
     *     2: ?string,
     *     3: ?\Exception,
     * }>
     */
    public function exporterProvider(): \Generator
    {
        yield [
            'stream+console://default',
            new MetricExporterOptions(),
            Temporality::DELTA,
            null,
        ];

        yield [
            'stream+console://default',
            new MetricExporterOptions(MetricTemporalityEnum::Cumulative),
            Temporality::CUMULATIVE,
            null,
        ];

        yield [
            'stream+console://default/var/log/symfony.log',
            new MetricExporterOptions(),
            Temporality::DELTA,
            null,
        ];

        yield [
            'stream+console://',
            new MetricExporterOptions(),
            null,
            new \InvalidArgumentException('The DSN is invalid.'),
        ];

        yield [
            'in-memory://default',
            new MetricExporterOptions(),
            Temporality::DELTA,
            null,
        ];

        yield [
            'http+otlp://default',
            new MetricExporterOptions(),
            Temporality::DELTA,
            null,
        ];

        yield [
            'grpc+otlp://default',
            new MetricExporterOptions(),
            Temporality::DELTA,
            null,
        ];

        yield [
            'noop://default',
            new MetricExporterOptions(),
            Temporality::DELTA,
            null,
        ];
    }
}
