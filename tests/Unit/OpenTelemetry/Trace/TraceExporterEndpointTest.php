<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Trace;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TraceExporterEndpoint;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(TraceExporterEndpoint::class)]
class TraceExporterEndpointTest extends TestCase
{
    #[DataProvider('dsnProvider')]
    public function testFromDsn(string $dsn, ?string $endpoint, ?\Exception $exception): void
    {
        if (null !== $exception) {
            self::expectExceptionObject($exception);
        }

        self::assertSame($endpoint, (string) TraceExporterEndpoint::fromDsn(ExporterDsn::fromString($dsn)));
    }

    /**
     * @return \Generator<int, array{
     *     0: string,
     *     1: ?string,
     *     2: ?\Exception
     * }>
     */
    public static function dsnProvider(): \Generator
    {
        yield [
            'http+otlp://localhost',
            'http://localhost:4318/v1/traces',
            null,
        ];

        yield [
            'http+otlp://localhost/v2/traces',
            'http://localhost:4318/v2/traces',
            null,
        ];

        yield [
            'http+otlp://localhost:4319',
            'http://localhost:4319/v1/traces',
            null,
        ];

        yield [
            'http+otlp://localhost:4319/v2/traces',
            'http://localhost:4319/v2/traces',
            null,
        ];

        yield [
            'http+otlp://test:test@localhost:4318/v1/traces',
            'http://test:test@localhost:4318/v1/traces',
            null,
        ];

        yield [
            'grpc+otlp://localhost',
            'http://localhost:4317/opentelemetry.proto.collector.trace.v1.TraceService/Export',
            null,
        ];

        yield [
            'grpc+otlp://localhost:4316',
            'http://localhost:4316/opentelemetry.proto.collector.trace.v1.TraceService/Export',
            null,
        ];

        yield [
            'grpc+otlp://localhost/opentelemetry.proto.collector.trace.v2.TraceService/Export',
            'http://localhost:4317/opentelemetry.proto.collector.trace.v2.TraceService/Export',
            null,
        ];

        yield [
            'stream+console://default',
            'php://stdout',
            null,
        ];

        yield [
            'stream+console://default/var/log/symfony.log',
            '/var/log/symfony.log',
            null,
        ];

        yield [
            'http+zipkin://localhost',
            'http://localhost:9411/api/v2/spans',
            null,
        ];

        yield [
            'in-memory://default',
            '',
            null,
        ];

        yield [
            'noop://default',
            '',
            new \InvalidArgumentException('Unsupported DSN exporter'),
        ];
    }
}
