<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Trace;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\ZipkinExporterEndpoint;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ZipkinExporterEndpoint::class)]
class ZipkinExporterEndpointTest extends TestCase
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
        $exporterEndpoint = ZipkinExporterEndpoint::fromDsn($dsn);

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
     *     ?\Exception
     * }>
     */
    public static function dsnProvider(): \Generator
    {
        yield 'http+zipkin' => [
            'http+zipkin://localhost',
            'zipkin',
            'http',
            'http://localhost:9411/api/v2/spans',
            null,
        ];

        yield 'http+zipkin_with-path' => [
            'http+zipkin://localhost/api/v3/spans',
            'zipkin',
            'http',
            'http://localhost:9411/api/v3/spans',
            null,
        ];

        yield 'http+zipkin_with-port' => [
            'http+zipkin://localhost:9412',
            'zipkin',
            'http',
            'http://localhost:9412/api/v2/spans',
            null,
        ];

        yield 'http+zipkin_with-path-port' => [
            'http+zipkin://localhost:9412/api/v3/spans',
            'zipkin',
            'http',
            'http://localhost:9412/api/v3/spans',
            null,
        ];

        yield 'http+zipkin_with-credentials' => [
            'http+zipkin://test:test@localhost:9411/api/v2/spans',
            'zipkin',
            'http',
            'http://test:test@localhost:9411/api/v2/spans',
            null,
        ];

        yield 'unsupported' => [
            'in-memory://default',
            'in-memory',
            null,
            null,
            new \InvalidArgumentException('Unsupported DSN exporter for this endpoint.'),
        ];
    }
}
