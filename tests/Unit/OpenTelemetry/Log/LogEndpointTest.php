<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Log;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporterEndpoint;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporterEndpoint
 */
class LogEndpointTest extends TestCase
{
    /**
     * @dataProvider dsnProvider
     */
    public function testFromDsn(string $dsn, string $endpoint): void
    {
        self::assertSame($endpoint, (string) LogExporterEndpoint::fromDsn(ExporterDsn::fromString($dsn)));
    }

    /**
     * @return \Generator<int, array{0: string, 1: string}>
     */
    public function dsnProvider(): \Generator
    {
        yield [
            'http+otlp://localhost',
            'http://localhost:4318/v1/logs',
        ];

        yield [
            'http+otlp://localhost/v2/logs',
            'http://localhost:4318/v2/logs',
        ];

        yield [
            'http+otlp://localhost:4319',
            'http://localhost:4319/v1/logs',
        ];

        yield [
            'http+otlp://localhost:4319/v2/logs',
            'http://localhost:4319/v2/logs',
        ];

        yield [
            'http+otlp://test:test@localhost:4318/v1/logs',
            'http://test:test@localhost:4318/v1/logs',
        ];

        yield [
            'grpc+otlp://localhost',
            'http://localhost:4317/opentelemetry.proto.collector.logs.v1.LogsService/Export',
        ];

        yield [
            'grpc+otlp://localhost:4316',
            'http://localhost:4316/opentelemetry.proto.collector.logs.v1.LogsService/Export',
        ];

        yield [
            'grpc+otlp://localhost/opentelemetry.proto.collector.logs.v2.LogsService/Export',
            'http://localhost:4317/opentelemetry.proto.collector.logs.v2.LogsService/Export',
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
