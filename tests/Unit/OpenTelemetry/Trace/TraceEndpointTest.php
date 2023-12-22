<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Trace;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TraceExporterEndpoint;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TraceExporterEndpoint
 */
class TraceEndpointTest extends TestCase
{
    /**
     * @dataProvider dsnProvider
     */
    public function testFromDsn(string $dsn, string $endpoint): void
    {
        self::assertSame($endpoint, (string) TraceExporterEndpoint::fromDsn(ExporterDsn::fromString($dsn)));
    }

    /**
     * @return \Generator<int, array{0: string, 1: string}>
     */
    public function dsnProvider(): \Generator
    {
        yield [
            'http+otlp://localhost',
            'http://localhost:4318/v1/traces',
        ];

        yield [
            'http+otlp://localhost/v2/traces',
            'http://localhost:4318/v2/traces',
        ];

        yield [
            'http+otlp://localhost:4319',
            'http://localhost:4319/v1/traces',
        ];

        yield [
            'http+otlp://localhost:4319/v2/traces',
            'http://localhost:4319/v2/traces',
        ];

        yield [
            'http+otlp://test:test@localhost:4318/v1/traces',
            'http://test:test@localhost:4318/v1/traces',
        ];

        yield [
            'grpc+otlp://localhost',
            'http://localhost:4317/opentelemetry.proto.collector.trace.v1.TraceService/Export',
        ];

        yield [
            'grpc+otlp://localhost:4316',
            'http://localhost:4316/opentelemetry.proto.collector.trace.v1.TraceService/Export',
        ];

        yield [
            'grpc+otlp://localhost/opentelemetry.proto.collector.trace.v2.TraceService/Export',
            'http://localhost:4317/opentelemetry.proto.collector.trace.v2.TraceService/Export',
        ];

        yield [
            'stream+console://default',
            'php://stdout',
        ];

        yield [
            'stream+console://default/var/logs/test.log',
            '/var/logs/test.log',
        ];
    }
}
