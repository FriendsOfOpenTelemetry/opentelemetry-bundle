<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Metric\MetricExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\ConsoleMetricExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\InMemoryMetricExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\MetricExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\NoopMetricExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\OtlpMetricExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\GrpcTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\OtlpHttpTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\PsrHttpTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\StreamTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportFactory;
use OpenTelemetry\Contrib\Grpc\GrpcTransport;
use OpenTelemetry\Contrib\Otlp\MetricExporter;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransport;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Metrics\MetricExporter\ConsoleMetricExporter;
use OpenTelemetry\SDK\Metrics\MetricExporter\InMemoryExporter;
use OpenTelemetry\SDK\Metrics\MetricExporter\NoopMetricExporter;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(MetricExporterFactory::class)]
class MetricExporterFactoryTest extends TestCase
{
    private function getTransportFactory(): TransportFactory
    {
        return new TransportFactory([
            new GrpcTransportFactory(),
            new OtlpHttpTransportFactory(),
            new PsrHttpTransportFactory(),
            new StreamTransportFactory(),
        ]);
    }

    #[DataProvider('exporterProvider')]
    public function testCreateExporter(
        string $dsn,
        ExporterOptionsInterface $options,
        ?string $exporterClass,
        ?string $transportClass,
    ): void {
        $exporterFactory = (new MetricExporterFactory([
            new ConsoleMetricExporterFactory($this->getTransportFactory()),
            new InMemoryMetricExporterFactory($this->getTransportFactory()),
            new NoopMetricExporterFactory($this->getTransportFactory()),
            new OtlpMetricExporterFactory($this->getTransportFactory()),
        ]));

        $exporter = $exporterFactory->createExporter(ExporterDsn::fromString($dsn), $options);

        self::assertInstanceOf($exporterClass, $exporter);

        if (null !== $transportClass) {
            $reflection = new \ReflectionObject($exporter);
            $transport = $reflection->getProperty('transport');

            self::assertInstanceOf($transportClass, $transport->getValue($exporter));
        }
    }

    /**
     * @return \Generator<array{
     *     0: string,
     *     1: ExporterOptionsInterface,
     *     2: class-string<MetricExporterInterface>,
     *     3: ?class-string<TransportInterface<string>>,
     * }>
     */
    public static function exporterProvider(): \Generator
    {
        yield [
            'stream+console://default',
            new MetricExporterOptions(),
            ConsoleMetricExporter::class,
            // This exporter has no transport
            null,
        ];

        yield [
            'in-memory://default',
            new MetricExporterOptions(),
            InMemoryExporter::class,
            null,
        ];

        yield [
            'noop://default',
            new MetricExporterOptions(),
            NoopMetricExporter::class,
            null,
        ];

        yield [
            'http+otlp://default',
            new MetricExporterOptions(),
            MetricExporter::class,
            PsrTransport::class,
        ];

        yield [
            'grpc+otlp://default',
            new MetricExporterOptions(),
            MetricExporter::class,
            GrpcTransport::class,
        ];
    }
}
