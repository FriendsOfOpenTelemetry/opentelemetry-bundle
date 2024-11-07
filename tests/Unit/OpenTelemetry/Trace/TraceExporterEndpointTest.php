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
        $traceEndpoint = TraceExporterEndpoint::fromDsn($dsn);

        self::assertSame($exporter, $traceEndpoint->getExporter());
        self::assertSame($transport, $traceEndpoint->getTransport());
        self::assertSame($endpoint, (string) $traceEndpoint);
    }

    /**
     * @return \Generator<string, array{
     *     string,
     *     string,
     *     ?string,
     *     ?string,
     *     ?\Exception
     * }>
     */
    public static function dsnProvider(): \Generator
    {
        yield 'http+otlp' => [
            'http+otlp://localhost',
            'otlp',
            'http',
            'http://localhost:4318/v1/traces',
            null,
        ];

        yield 'http+otlp_with-path' => [
            'http+otlp://localhost/v2/traces',
            'otlp',
            'http',
            'http://localhost:4318/v2/traces',
            null,
        ];

        yield 'http+otlp_with-port' => [
            'http+otlp://localhost:4319',
            'otlp',
            'http',
            'http://localhost:4319/v1/traces',
            null,
        ];

        yield 'http+otlp_with-path-port' => [
            'http+otlp://localhost:4319/v2/traces',
            'otlp',
            'http',
            'http://localhost:4319/v2/traces',
            null,
        ];

        yield 'http+otlp_with-credentials' => [
            'http+otlp://test:test@localhost:4318/v1/traces',
            'otlp',
            'http',
            'http://test:test@localhost:4318/v1/traces',
            null,
        ];

        yield 'grpc+otlp' => [
            'grpc+otlp://localhost',
            'otlp',
            'grpc',
            'http://localhost:4317/opentelemetry.proto.collector.trace.v1.TraceService/Export',
            null,
        ];

        yield 'grpc+otlp_with-port' => [
            'grpc+otlp://localhost:4316',
            'otlp',
            'grpc',
            'http://localhost:4316/opentelemetry.proto.collector.trace.v1.TraceService/Export',
            null,
        ];

        yield 'grpc+otlp_with-path-port' => [
            'grpc+otlp://localhost/opentelemetry.proto.collector.trace.v2.TraceService/Export',
            'otlp',
            'grpc',
            'http://localhost:4317/opentelemetry.proto.collector.trace.v2.TraceService/Export',
            null,
        ];

        yield 'stream+console' => [
            'stream+console://default',
            'console',
            'stream',
            'php://stdout',
            null,
        ];

        yield 'stream+console_with+path' => [
            'stream+console://default/var/log/symfony.log',
            'console',
            'stream',
            '/var/log/symfony.log',
            null,
        ];

        yield 'http+zipkin' => [
            'http+zipkin://localhost',
            'zipkin',
            'http',
            'http://localhost:9411/api/v2/spans',
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
            new \InvalidArgumentException('Unsupported DSN for Trace exporter'),
        ];

        yield 'unsupported' => [
            'foo://default',
            'foo',
            null,
            '',
            new \InvalidArgumentException('Unsupported DSN for Trace exporter'),
        ];
    }
}
