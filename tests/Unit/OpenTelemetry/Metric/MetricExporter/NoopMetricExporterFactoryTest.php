<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Metric\MetricExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\NoopMetricExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopMetricExporterFactory::class)]
class NoopMetricExporterFactoryTest extends TestCase
{
    #[DataProvider('exporterProvider')]
    public function testCreateExporter(string $dsn, ExporterOptionsInterface $options, ?\Exception $exception): void
    {
        if (null !== $exception) {
            self::expectExceptionObject($exception);
        } else {
            self::expectNotToPerformAssertions();
        }

        (new NoopMetricExporterFactory(new TransportFactory([])))->createExporter(ExporterDsn::fromString($dsn), $options);
    }

    /**
     * @return \Generator<array{
     *     0: string,
     *     1: ExporterOptionsInterface,
     *     2: ?\Exception,
     * }>
     */
    public static function exporterProvider(): \Generator
    {
        yield [
            'stream+console://default',
            new MetricExporterOptions(),
            null,
        ];

        yield [
            'stream+console://default',
            new MetricExporterOptions(),
            null,
        ];

        yield [
            'stream+console://default/var/log/symfony.log',
            new MetricExporterOptions(),
            null,
        ];

        yield [
            'stream+console://',
            new MetricExporterOptions(),
            new \InvalidArgumentException('The DSN is invalid.'),
        ];

        yield [
            'in-memory://default',
            new MetricExporterOptions(),
            null,
        ];

        yield [
            'http+otlp://default',
            new MetricExporterOptions(),
            null,
        ];

        yield [
            'grpc+otlp://default',
            new MetricExporterOptions(),
            null,
        ];

        yield [
            'noop://default',
            new MetricExporterOptions(),
            null,
        ];
    }
}
