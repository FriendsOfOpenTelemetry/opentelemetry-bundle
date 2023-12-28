<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Metric\MetricExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\OtlpMetricExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporterOptions;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\OtlpMetricExporterFactory
 */
class OtlpMetricExporterFactoryTest extends TestCase
{
    /**
     * @dataProvider exporterProvider
     */
    public function testCreateExporter(string $dsn, ExporterOptionsInterface $options, ?string $temporality, ?\Exception $exception): void
    {
        if (null !== $exception) {
            self::expectExceptionObject($exception);
        }

        $exporter = OtlpMetricExporterFactory::createExporter(ExporterDsn::fromString($dsn), $options);

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
            null,
            new \InvalidArgumentException('DSN exporter must be of type Otlp.'),
        ];

        yield [
            'stream+console://default',
            new MetricExporterOptions(),
            null,
            new \InvalidArgumentException('DSN exporter must be of type Otlp.'),
        ];

        yield [
            'in-memory://default',
            new MetricExporterOptions(),
            null,
            new \InvalidArgumentException('DSN exporter must be of type Otlp.'),
        ];

        yield [
            'http+zipkin://default',
            new MetricExporterOptions(),
            null,
            new \InvalidArgumentException('Unsupported DSN exporter.'),
        ];
    }
}
