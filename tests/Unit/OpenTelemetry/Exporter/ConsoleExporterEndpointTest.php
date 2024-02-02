<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Exporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ConsoleExporterEndpoint;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConsoleExporterEndpoint::class)]
class ConsoleExporterEndpointTest extends TestCase
{
    #[DataProvider('dsnProvider')]
    public function testFromDsn(string $dsn, string $endpoint): void
    {
        self::assertSame($endpoint, (string) ConsoleExporterEndpoint::fromDsn(ExporterDsn::fromString($dsn)));
    }

    /**
     * @return \Generator<int, array{0: string, 1: string}>
     */
    public static function dsnProvider(): \Generator
    {
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
