<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Metric\MetricExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\OtlpMetricExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\GrpcTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\OtlpHttpTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportFactory;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(OtlpMetricExporterFactory::class)]
class OtlpMetricExporterFactoryTest extends TestCase
{
    #[DataProvider('exporterProvider')]
    public function testCreateExporter(string $dsn, ExporterOptionsInterface $options, ?string $temporality, bool $supports): void
    {
        $dsn = ExporterDsn::fromString($dsn);
        $exporterFactory = new OtlpMetricExporterFactory(new TransportFactory([
            new GrpcTransportFactory(),
            new OtlpHttpTransportFactory(),
        ]));
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
     * @return \Generator<string, array{
     *     0: string,
     *     1: ExporterOptionsInterface,
     *     2: ?string,
     *     3: bool,
     * }>
     */
    public static function exporterProvider(): \Generator
    {
        yield 'http+otlp' => [
            'http+otlp://default',
            new MetricExporterOptions(),
            Temporality::DELTA,
            true,
        ];

        yield 'grpc+otlp' => [
            'grpc+otlp://default',
            new MetricExporterOptions(),
            Temporality::DELTA,
            true,
        ];

        yield 'no transport' => [
            'noop://default',
            new MetricExporterOptions(),
            null,
            false,
        ];

        yield 'unsupported transport' => [
            'stream+console://default',
            new MetricExporterOptions(),
            null,
            false,
        ];

        yield 'unsupported dsn' => [
            'http+zipkin://default',
            new MetricExporterOptions(),
            null,
            false,
        ];

        yield 'unsupported options' => [
            'http+zipkin://default',
            new OtlpExporterOptions(),
            null,
            false,
        ];
    }
}
