<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Metric\MetricExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\ConsoleMetricExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\MetricTemporalityEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportFactory;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConsoleMetricExporterFactory::class)]
class ConsoleMetricExporterFactoryTest extends TestCase
{
    #[DataProvider('exporterProvider')]
    public function testCreateExporter(string $dsn, ExporterOptionsInterface $options, ?string $temporality, bool $supports): void
    {
        $dsn = ExporterDsn::fromString($dsn);
        $exporterFactory = (new ConsoleMetricExporterFactory(new TransportFactory([])));

        self::assertEquals($supports, $exporterFactory->supports($dsn, $options));
        if (false === $supports) {
            return;
        }

        $exporter = $exporterFactory->createExporter($dsn, $options);

        $reflection = new \ReflectionObject($exporter);
        $reflectedTemporality = $reflection->getProperty('temporality');

        self::assertSame($temporality, $reflectedTemporality->getValue($exporter));
    }

    /**
     * @return \Generator<array{
     *     0: string,
     *     1: ExporterOptionsInterface,
     *     2: ?string,
     *     3: bool,
     * }>
     */
    public static function exporterProvider(): \Generator
    {
        yield [
            'stream+console://default',
            new MetricExporterOptions(),
            Temporality::DELTA,
            true,
        ];

        yield [
            'stream+console://default',
            new MetricExporterOptions(MetricTemporalityEnum::Cumulative),
            Temporality::CUMULATIVE,
            true,
        ];

        yield [
            'stream+console://default/var/log/symfony.log',
            new MetricExporterOptions(),
            Temporality::DELTA,
            true,
        ];

        yield [
            'stream+console://default',
            new OtlpExporterOptions(),
            Temporality::DELTA,
            false,
        ];

        yield [
            'in-memory://default',
            new MetricExporterOptions(),
            Temporality::DELTA,
            false,
        ];

        yield [
            'http+otlp://default',
            new MetricExporterOptions(),
            Temporality::DELTA,
            false,
        ];

        yield [
            'grpc+otlp://default',
            new MetricExporterOptions(),
            Temporality::DELTA,
            false,
        ];

        yield [
            'noop://default',
            new MetricExporterOptions(),
            Temporality::DELTA,
            false,
        ];
    }
}
