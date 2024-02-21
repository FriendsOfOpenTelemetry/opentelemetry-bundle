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
    public function testCreateExporter(string $dsn, ExporterOptionsInterface $options, bool $supports): void
    {
        $dsn = ExporterDsn::fromString($dsn);
        $exporterFactory = (new NoopMetricExporterFactory(new TransportFactory([])));
        self::assertEquals($supports, $exporterFactory->supports($dsn, $options));
        if (false === $supports) {
            return;
        }

        $exporterFactory->createExporter($dsn, $options);
    }

    /**
     * @return \Generator<string, array{
     *     0: string,
     *     1: ExporterOptionsInterface,
     *     2: bool,
     * }>
     */
    public static function exporterProvider(): \Generator
    {
        yield 'default' => [
            'noop://default',
            new MetricExporterOptions(),
            true,
        ];

        yield 'unsupported dsn' => [
            'foo://default',
            new MetricExporterOptions(),
            false,
        ];
    }
}
