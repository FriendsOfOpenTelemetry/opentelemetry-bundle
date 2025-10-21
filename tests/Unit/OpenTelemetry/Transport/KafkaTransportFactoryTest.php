<?php

declare(strict_types=1);

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
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\KafkaTransportFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(KafkaTransportFactory::class)]
final class KafkaTransportFactoryTest extends TestCase
{
    #[DataProvider('exporterProvider')]
    public function testCreateTransportFromExporter(
        ExporterEndpointInterface $endpoint,
        ExporterOptionsInterface $options,
        bool $shouldSupport,
    ): void {
        $factory = new KafkaTransportFactory();

        self::assertSame($shouldSupport, $factory->supports($endpoint, $options));

        if ($shouldSupport) {
            $transport = $factory->createTransport($endpoint, $options);
            self::assertSame('application/x-protobuf', $transport->contentType());
        }
    }

    /**
     * @return \Generator<array{
     *     0: ExporterEndpointInterface,
     *     1: ExporterOptionsInterface,
     *     2: bool
     * }>
     */
    public static function exporterProvider(): \Generator
    {
        // Kafka for traces
        yield [
            TraceExporterEndpoint::fromDsn(ExporterDsn::fromString('kafka+otlp://otel-traces?metadata_broker_list=localhost:9092')),
            new OtlpExporterOptions(),
            true,
        ];

        // Kafka for metrics
        yield [
            MetricExporterEndpoint::fromDsn(ExporterDsn::fromString('kafka+otlp://otel-metrics?metadata_broker_list=localhost:9092')),
            new MetricExporterOptions(),
            true,
        ];

        // Kafka for logs
        yield [
            LogExporterEndpoint::fromDsn(ExporterDsn::fromString('kafka+otlp://otel-logs?metadata_broker_list=localhost:9092')),
            new OtlpExporterOptions(),
            true,
        ];

        // Not Kafka transports should not be supported
        yield [
            TraceExporterEndpoint::fromDsn(ExporterDsn::fromString('grpc+otlp://localhost')),
            new OtlpExporterOptions(),
            false,
        ];

        yield [
            TraceExporterEndpoint::fromDsn(ExporterDsn::fromString('http+otlp://localhost')),
            new OtlpExporterOptions(),
            false,
        ];

        yield [
            TraceExporterEndpoint::fromDsn(ExporterDsn::fromString('stream+console://default')),
            new EmptyExporterOptions(),
            false,
        ];
    }
}
