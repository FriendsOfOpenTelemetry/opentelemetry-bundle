<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterEndpoint;
use OpenTelemetry\API\Signals;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterEndpoint
 */
class OtlpEndpointTest extends TestCase
{
    /**
     * @dataProvider dsnProvider
     */
    public function testFromDsn(string $dsn, string $endpoint, string $signal): void
    {
        self::assertSame($endpoint, (string) OtlpExporterEndpoint::fromDsn(ExporterDsn::fromString($dsn))->withSignal($signal));
    }

    /**
     * @return \Generator<int, array{0: string, 1: string, 2: string}>
     */
    public function dsnProvider(): \Generator
    {
        yield [
            'http+otlp://localhost',
            'http://localhost:4318/v1/logs',
            Signals::LOGS,
        ];

        yield [
            'http+otlp://localhost/v2/logs',
            'http://localhost:4318/v2/logs',
            Signals::LOGS,
        ];

        yield [
            'http+otlp://localhost:4319',
            'http://localhost:4319/v1/logs',
            Signals::LOGS,
        ];

        yield [
            'http+otlp://localhost:4319/v2/logs',
            'http://localhost:4319/v2/logs',
            Signals::LOGS,
        ];

        yield [
            'http+otlp://test:test@localhost:4318/v1/logs',
            'http://test:test@localhost:4318/v1/logs',
            Signals::LOGS,
        ];

        yield [
            'grpc+otlp://localhost',
            'http://localhost:4317/opentelemetry.proto.collector.logs.v1.LogsService/Export',
            Signals::LOGS,
        ];

        yield [
            'grpc+otlp://localhost:4316',
            'http://localhost:4316/opentelemetry.proto.collector.logs.v1.LogsService/Export',
            Signals::LOGS,
        ];

        yield [
            'grpc+otlp://localhost/opentelemetry.proto.collector.logs.v2.LogsService/Export',
            'http://localhost:4317/opentelemetry.proto.collector.logs.v2.LogsService/Export',
            Signals::LOGS,
        ];

        yield [
            'http+otlp://localhost',
            'http://localhost:4318/v1/metrics',
            Signals::METRICS,
        ];

        yield [
            'http+otlp://localhost/v2/metrics',
            'http://localhost:4318/v2/metrics',
            Signals::METRICS,
        ];

        yield [
            'http+otlp://localhost:4319',
            'http://localhost:4319/v1/metrics',
            Signals::METRICS,
        ];

        yield [
            'http+otlp://localhost:4319/v2/metrics',
            'http://localhost:4319/v2/metrics',
            Signals::METRICS,
        ];

        yield [
            'http+otlp://test:test@localhost:4318/v1/metrics',
            'http://test:test@localhost:4318/v1/metrics',
            Signals::METRICS,
        ];

        yield [
            'grpc+otlp://localhost',
            'http://localhost:4317/opentelemetry.proto.collector.metrics.v1.MetricsService/Export',
            Signals::METRICS,
        ];

        yield [
            'grpc+otlp://localhost:4316',
            'http://localhost:4316/opentelemetry.proto.collector.metrics.v1.MetricsService/Export',
            Signals::METRICS,
        ];

        yield [
            'grpc+otlp://localhost/opentelemetry.proto.collector.metrics.v2.MetricsService/Export',
            'http://localhost:4317/opentelemetry.proto.collector.metrics.v2.MetricsService/Export',
            Signals::METRICS,
        ];

        yield [
            'http+otlp://localhost',
            'http://localhost:4318/v1/traces',
            Signals::TRACE,
        ];

        yield [
            'http+otlp://localhost/v2/traces',
            'http://localhost:4318/v2/traces',
            Signals::TRACE,
        ];

        yield [
            'http+otlp://localhost:4319',
            'http://localhost:4319/v1/traces',
            Signals::TRACE,
        ];

        yield [
            'http+otlp://localhost:4319/v2/traces',
            'http://localhost:4319/v2/traces',
            Signals::TRACE,
        ];

        yield [
            'http+otlp://test:test@localhost:4318/v1/traces',
            'http://test:test@localhost:4318/v1/traces',
            Signals::TRACE,
        ];

        yield [
            'grpc+otlp://localhost',
            'http://localhost:4317/opentelemetry.proto.collector.trace.v1.TraceService/Export',
            Signals::TRACE,
        ];

        yield [
            'grpc+otlp://localhost:4316',
            'http://localhost:4316/opentelemetry.proto.collector.trace.v1.TraceService/Export',
            Signals::TRACE,
        ];

        yield [
            'grpc+otlp://localhost/opentelemetry.proto.collector.trace.v2.TraceService/Export',
            'http://localhost:4317/opentelemetry.proto.collector.trace.v2.TraceService/Export',
            Signals::TRACE,
        ];
    }
}
