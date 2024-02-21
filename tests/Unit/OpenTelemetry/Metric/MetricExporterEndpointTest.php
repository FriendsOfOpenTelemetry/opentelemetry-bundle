<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Metric;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporterEndpoint;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(MetricExporterEndpoint::class)]
class MetricExporterEndpointTest extends TestCase
{
    #[DataProvider('dsnProvider')]
    public function testFromDsn(string $dsn, ?string $endpoint, ?\Exception $exception): void
    {
        if (null !== $exception) {
            self::expectExceptionObject($exception);
        }

        self::assertSame($endpoint, (string) MetricExporterEndpoint::fromDsn(ExporterDsn::fromString($dsn)));
    }

    /**
     * @return \Generator<string, array{
     *     0: string,
     *     1: ?string,
     *     2: ?\Exception,
     * }>
     */
    public static function dsnProvider(): \Generator
    {
        yield 'http+otlp' => [
            'http+otlp://localhost',
            'http://localhost:4318/v1/metrics',
            null,
        ];

        yield 'http+otlp_with-url' => [
            'http+otlp://localhost/v2/metrics',
            'http://localhost:4318/v2/metrics',
            null,
        ];

        yield 'http+otlp_with-port' => [
            'http+otlp://localhost:4319',
            'http://localhost:4319/v1/metrics',
            null,
        ];

        yield 'http+otlp_with-url-port' => [
            'http+otlp://localhost:4319/v2/metrics',
            'http://localhost:4319/v2/metrics',
            null,
        ];

        yield 'http+otlp_with_credentials' => [
            'http+otlp://test:test@localhost:4318/v1/metrics',
            'http://test:test@localhost:4318/v1/metrics',
            null,
        ];

        yield 'grpc+otlp' => [
            'grpc+otlp://localhost',
            'http://localhost:4317/opentelemetry.proto.collector.metrics.v1.MetricsService/Export',
            null,
        ];

        yield 'grpc+otlp_with-port' => [
            'grpc+otlp://localhost:4316',
            'http://localhost:4316/opentelemetry.proto.collector.metrics.v1.MetricsService/Export',
            null,
        ];

        yield 'grpc+otlp_with-url' => [
            'grpc+otlp://localhost/opentelemetry.proto.collector.metrics.v2.MetricsService/Export',
            'http://localhost:4317/opentelemetry.proto.collector.metrics.v2.MetricsService/Export',
            null,
        ];

        yield 'stream+console' => [
            'stream+console://default',
            'php://stdout',
            null,
        ];

        yield 'stream+console_with-path' => [
            'stream+console://default/var/log/symfony.log',
            '/var/log/symfony.log',
            null,
        ];

        yield 'in-memory' => [
            'in-memory://default',
            '',
            null,
        ];

        yield 'noop' => [
            'noop://default',
            '',
            null,
        ];

        yield 'unsupported dsn' => [
            'foo://default',
            '',
            new \InvalidArgumentException('Unsupported DSN for Metric exporter'),
        ];
    }
}
