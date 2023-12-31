<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Metric;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporterEndpoint;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporterEndpoint
 */
class MetricExporterEndpointTest extends TestCase
{
    /**
     * @dataProvider dsnProvider
     */
    public function testFromDsn(string $dsn, ?string $endpoint, ?\Exception $exception): void
    {
        if (null !== $exception) {
            self::expectExceptionObject($exception);
        }

        self::assertSame($endpoint, (string) MetricExporterEndpoint::fromDsn(ExporterDsn::fromString($dsn)));
    }

    /**
     * @return \Generator<int, array{
     *     0: string,
     *     1: ?string,
     *     2: ?\Exception,
     * }>
     */
    public function dsnProvider(): \Generator
    {
        yield [
            'http+otlp://localhost',
            'http://localhost:4318/v1/metrics',
            null,
        ];

        yield [
            'http+otlp://localhost/v2/metrics',
            'http://localhost:4318/v2/metrics',
            null,
        ];

        yield [
            'http+otlp://localhost:4319',
            'http://localhost:4319/v1/metrics',
            null,
        ];

        yield [
            'http+otlp://localhost:4319/v2/metrics',
            'http://localhost:4319/v2/metrics',
            null,
        ];

        yield [
            'http+otlp://test:test@localhost:4318/v1/metrics',
            'http://test:test@localhost:4318/v1/metrics',
            null,
        ];

        yield [
            'grpc+otlp://localhost',
            'http://localhost:4317/opentelemetry.proto.collector.metrics.v1.MetricsService/Export',
            null,
        ];

        yield [
            'grpc+otlp://localhost:4316',
            'http://localhost:4316/opentelemetry.proto.collector.metrics.v1.MetricsService/Export',
            null,
        ];

        yield [
            'grpc+otlp://localhost/opentelemetry.proto.collector.metrics.v2.MetricsService/Export',
            'http://localhost:4317/opentelemetry.proto.collector.metrics.v2.MetricsService/Export',
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
            null,
            new \InvalidArgumentException('Unsupported DSN exporter'),
        ];

        yield [
            'in-memory://default',
            '',
            null,
        ];

        yield [
            'noop://default',
            '',
            null,
        ];
    }
}
