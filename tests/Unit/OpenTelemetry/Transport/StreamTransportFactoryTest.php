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
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\StreamTransportFactory
 */
class StreamTransportFactoryTest extends TestCase
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

        $factory = StreamTransportFactory::fromExporter($endpoint, $options);

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
            TraceExporterEndpoint::fromDsn(ExporterDsn::fromString('stream+console://default')),
            new EmptyExporterOptions(),
            null,
        ];

        yield [
            // This DSN is valid but given the context of the transport, the failure is expected.
            TraceExporterEndpoint::fromDsn(ExporterDsn::fromString('stream+console://default/var/log/symfony.log')),
            new EmptyExporterOptions(),
            new \ErrorException('fopen(/var/log/symfony.log): Failed to open stream: Permission denied'),
        ];

        yield [
            TraceExporterEndpoint::fromDsn(ExporterDsn::fromString('in-memory://default')),
            new EmptyExporterOptions(),
            new \InvalidArgumentException('Unsupported exporter endpoint or options for this transport.'),
        ];

        yield [
            TraceExporterEndpoint::fromDsn(ExporterDsn::fromString('http+otlp://localhost')),
            new OtlpExporterOptions(),
            new \InvalidArgumentException('Unsupported exporter endpoint or options for this transport.'),
        ];

        yield [
            MetricExporterEndpoint::fromDsn(ExporterDsn::fromString('http+otlp://localhost')),
            new MetricExporterOptions(),
            new \InvalidArgumentException('Unsupported exporter endpoint or options for this transport.'),
        ];

        yield [
            LogExporterEndpoint::fromDsn(ExporterDsn::fromString('http+otlp://localhost')),
            new OtlpExporterOptions(),
            new \InvalidArgumentException('Unsupported exporter endpoint or options for this transport.'),
        ];

        yield [
            ZipkinExporterEndpoint::fromDsn(ExporterDsn::fromString('http+zipkin://localhost')),
            new EmptyExporterOptions(),
            new \InvalidArgumentException('Unsupported exporter endpoint or options for this transport.'),
        ];

        yield [
            TraceExporterEndpoint::fromDsn(ExporterDsn::fromString('grpc+otlp://localhost')),
            new OtlpExporterOptions(),
            new \InvalidArgumentException('Unsupported exporter endpoint or options for this transport.'),
        ];
    }
}
