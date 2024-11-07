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
    public function testFromDsn(
        string $dsn,
        string $exporter,
        ?string $transport,
        ?string $endpoint,
        ?\Exception $exception,
    ): void {
        if (null !== $exception) {
            self::expectExceptionObject($exception);
        }

        $dsn = ExporterDsn::fromString($dsn);
        $exporterEndpoint = MetricExporterEndpoint::fromDsn($dsn);

        self::assertSame($exporter, $exporterEndpoint->getExporter());
        self::assertSame($transport, $exporterEndpoint->getTransport());
        self::assertSame($endpoint, (string) $exporterEndpoint);
    }

    /**
     * @return \Generator<string, array{
     *     string,
     *     string,
     *     ?string,
     *     ?string,
     *     ?\Exception,
     * }>
     */
    public static function dsnProvider(): \Generator
    {
        yield 'http+otlp' => [
            'http+otlp://localhost',
            'otlp',
            'http',
            'http://localhost:4318/v1/metrics',
            null,
        ];

        yield 'http+otlp_with-url' => [
            'http+otlp://localhost/v2/metrics',
            'otlp',
            'http',
            'http://localhost:4318/v2/metrics',
            null,
        ];

        yield 'http+otlp_with-port' => [
            'http+otlp://localhost:4319',
            'otlp',
            'http',
            'http://localhost:4319/v1/metrics',
            null,
        ];

        yield 'http+otlp_with-url-port' => [
            'http+otlp://localhost:4319/v2/metrics',
            'otlp',
            'http',
            'http://localhost:4319/v2/metrics',
            null,
        ];

        yield 'http+otlp_with_credentials' => [
            'http+otlp://test:test@localhost:4318/v1/metrics',
            'otlp',
            'http',
            'http://test:test@localhost:4318/v1/metrics',
            null,
        ];

        yield 'grpc+otlp' => [
            'grpc+otlp://localhost',
            'otlp',
            'grpc',
            'http://localhost:4317/opentelemetry.proto.collector.metrics.v1.MetricsService/Export',
            null,
        ];

        yield 'grpc+otlp_with-port' => [
            'grpc+otlp://localhost:4316',
            'otlp',
            'grpc',
            'http://localhost:4316/opentelemetry.proto.collector.metrics.v1.MetricsService/Export',
            null,
        ];

        yield 'grpc+otlp_with-url' => [
            'grpc+otlp://localhost/opentelemetry.proto.collector.metrics.v2.MetricsService/Export',
            'otlp',
            'grpc',
            'http://localhost:4317/opentelemetry.proto.collector.metrics.v2.MetricsService/Export',
            null,
        ];

        yield 'stream+console' => [
            'stream+console://default',
            'console',
            'stream',
            'php://stdout',
            null,
        ];

        yield 'stream+console_with-path' => [
            'stream+console://default/var/log/symfony.log',
            'console',
            'stream',
            '/var/log/symfony.log',
            null,
        ];

        yield 'in-memory' => [
            'in-memory://default',
            'in-memory',
            null,
            '',
            null,
        ];

        yield 'noop' => [
            'noop://default',
            'noop',
            null,
            '',
            null,
        ];

        yield 'unsupported' => [
            'foo://default',
            'foo',
            null,
            '',
            new \InvalidArgumentException('Unsupported DSN for Metric exporter'),
        ];
    }
}
