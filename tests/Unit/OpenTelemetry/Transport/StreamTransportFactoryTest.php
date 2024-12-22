<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Transport;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\EmptyExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterEndpointInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporterEndpoint;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporterEndpoint;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TraceExporterEndpoint;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\ZipkinExporterEndpoint;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\StreamTransportFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(StreamTransportFactory::class)]
class StreamTransportFactoryTest extends TestCase
{
    #[DataProvider('exporterProvider')]
    public function testCreateTransportFromExporter(
        ExporterEndpointInterface $endpoint,
        ExporterOptionsInterface $options,
        bool $shouldSupport,
    ): void {
        $factory = new StreamTransportFactory();

        self::assertSame($shouldSupport, $factory->supports($endpoint, $options));

        if ($shouldSupport) {
            $transport = $factory->createTransport($endpoint, $options);
            self::assertSame('application/json', $transport->contentType());
        }
    }

    /**
     * @return \Generator<array{
     *     0: ExporterEndpointInterface,
     *     1: ExporterOptionsInterface,
     *     2: bool,
     * }>
     */
    public static function exporterProvider(): \Generator
    {
        yield [
            TraceExporterEndpoint::fromDsn(ExporterDsn::fromString('stream+console://default')),
            new EmptyExporterOptions(),
            true,
        ];

        yield [
            TraceExporterEndpoint::fromDsn(ExporterDsn::fromString('stream+console://default/tmp/symfony.log')),
            new EmptyExporterOptions(),
            true,
        ];

        yield [
            TraceExporterEndpoint::fromDsn(ExporterDsn::fromString('in-memory://default')),
            new EmptyExporterOptions(),
            false,
        ];

        yield [
            TraceExporterEndpoint::fromDsn(ExporterDsn::fromString('http+otlp://localhost')),
            new OtlpExporterOptions(),
            false,
        ];

        yield [
            MetricExporterEndpoint::fromDsn(ExporterDsn::fromString('http+otlp://localhost')),
            new MetricExporterOptions(),
            false,
        ];

        yield [
            LogExporterEndpoint::fromDsn(ExporterDsn::fromString('http+otlp://localhost')),
            new OtlpExporterOptions(),
            false,
        ];

        yield [
            ZipkinExporterEndpoint::fromDsn(ExporterDsn::fromString('http+zipkin://localhost')),
            new EmptyExporterOptions(),
            false,
        ];

        yield [
            TraceExporterEndpoint::fromDsn(ExporterDsn::fromString('grpc+otlp://localhost')),
            new OtlpExporterOptions(),
            false,
        ];
    }
}
