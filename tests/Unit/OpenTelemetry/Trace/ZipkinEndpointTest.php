<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Trace;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\ZipkinExporterEndpoint;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TraceExporterEndpoint
 */
class ZipkinEndpointTest extends TestCase
{
    /**
     * @dataProvider dsnProvider
     */
    public function testFromDsn(string $dsn, string $endpoint): void
    {
        self::assertSame($endpoint, (string) ZipkinExporterEndpoint::fromDsn(ExporterDsn::fromString($dsn)));
    }

    /**
     * @return \Generator<int, array{0: string, 1: string}>
     */
    public function dsnProvider(): \Generator
    {
        yield [
            'http+zipkin://localhost',
            'http://localhost:9411/api/v2/spans',
        ];

        yield [
            'http+zipkin://localhost/api/v3/spans',
            'http://localhost:9411/api/v3/spans',
        ];

        yield [
            'http+zipkin://localhost:9412',
            'http://localhost:9412/api/v2/spans',
        ];

        yield [
            'http+zipkin://localhost:9412/api/v3/spans',
            'http://localhost:9412/api/v3/spans',
        ];

        yield [
            'http+zipkin://test:test@localhost:9411/api/v2/spans',
            'http://test:test@localhost:9411/api/v2/spans',
        ];
    }
}
