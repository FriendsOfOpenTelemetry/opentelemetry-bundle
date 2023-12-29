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
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\OtlpHttpTransportFactory;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\OtlpHttpTransportFactory
 */
class OtlpHttpTransportFactoryTest extends TestCase
{
    /**
     * @dataProvider exporterProvider
     */
    public function testCreateTransportFromExporter(
        ExporterEndpointInterface $endpoint,
        ExporterOptionsInterface $options,
        ?\Exception $exception,
    ): void {
        if (null !== $exception) {
            self::expectExceptionObject($exception);
        } else {
            $this->expectNotToPerformAssertions();
        }

        $factory = OtlpHttpTransportFactory::fromExporter($endpoint, $options);

        $factory->createTransport();
    }

    /**
     * @return \Generator<array{
     *     0: ExporterEndpointInterface,
     *     1: ExporterOptionsInterface,
     *     2: ?\Exception
     * }>
     */
    public function exporterProvider(): \Generator
    {
        yield [
            TraceExporterEndpoint::fromDsn(ExporterDsn::fromString('http+otlp://localhost')),
            new OtlpExporterOptions(),
            null,
        ];

        yield [
            MetricExporterEndpoint::fromDsn(ExporterDsn::fromString('http+otlp://localhost')),
            new MetricExporterOptions(),
            null,
        ];

        yield [
            LogExporterEndpoint::fromDsn(ExporterDsn::fromString('http+otlp://localhost')),
            new OtlpExporterOptions(),
            null,
        ];

        yield [
            ZipkinExporterEndpoint::fromDsn(ExporterDsn::fromString('http+zipkin://localhost')),
            new OtlpExporterOptions(),
            new \InvalidArgumentException('Unsupported exporter endpoint or options for this transport.'),
        ];

        yield [
            TraceExporterEndpoint::fromDsn(ExporterDsn::fromString('grpc+otlp://localhost')),
            new OtlpExporterOptions(),
            new \InvalidArgumentException('Unsupported exporter endpoint or options for this transport.'),
        ];

        yield [
            TraceExporterEndpoint::fromDsn(ExporterDsn::fromString('stream+console://default')),
            new EmptyExporterOptions(),
            new \InvalidArgumentException('Unsupported exporter endpoint or options for this transport.'),
        ];

        yield [
            TraceExporterEndpoint::fromDsn(ExporterDsn::fromString('in-memory://default')),
            new EmptyExporterOptions(),
            new \InvalidArgumentException('Unsupported exporter endpoint or options for this transport.'),
        ];
    }
}
