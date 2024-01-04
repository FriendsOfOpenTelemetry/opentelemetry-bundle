<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Exporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn
 */
class ExporterDsnTest extends TestCase
{
    /**
     * @dataProvider fromStringProvider
     *
     * @param array{transport?: string, exporter?: string} $expected
     */
    public function testFromString(ExporterDsn $dsn, array $expected): void
    {
        self::assertEquals($expected, [
            'transport' => $dsn->getTransport(),
            'exporter' => $dsn->getExporter(),
            'user' => $dsn->getUser(),
            'password' => $dsn->getPassword(),
            'host' => $dsn->getHost(),
            'port' => $dsn->getPort(),
            'path' => $dsn->getPath(),
        ]);
    }

    /**
     * @return iterable<string, array{0: ExporterDsn, 1: array{
     *     transport: ?string,
     *     exporter: string,
     *     user: ?string,
     *     password: ?string,
     *     host: string,
     *     port: ?int,
     *     path: ?string
     * }}>
     */
    public static function fromStringProvider(): iterable
    {
        yield 'gRPC Transport, OTLP Exporter' => [
            ExporterDsn::fromString('grpc+otlp://localhost:4317'),
            ['transport' => 'grpc', 'exporter' => 'otlp', 'user' => null, 'password' => null, 'host' => 'localhost', 'port' => 4317, 'path' => null],
        ];
        yield 'HTTP Transport, OTLP Exporter' => [
            ExporterDsn::fromString('http+otlp://localhost:4318/v1/traces'),
            ['transport' => 'http', 'exporter' => 'otlp', 'user' => null, 'password' => null, 'host' => 'localhost', 'port' => 4318, 'path' => '/v1/traces'],
        ];
        yield 'HTTP Transport, Zipkin Exporter with basic authentication' => [
            ExporterDsn::fromString('http+zipkin://user:password@localhost:9411/api/v2/spans'),
            ['transport' => 'http', 'exporter' => 'zipkin', 'user' => 'user', 'password' => 'password', 'host' => 'localhost', 'port' => 9411, 'path' => '/api/v2/spans'],
        ];
        yield 'Stream Transport, Console Exporter' => [
            ExporterDsn::fromString('stream+console://default'),
            ['transport' => 'stream', 'exporter' => 'console', 'user' => null, 'password' => null, 'host' => 'default', 'port' => null, 'path' => null],
        ];
        yield 'Memory Exporter' => [
            ExporterDsn::fromString('memory://default'),
            ['transport' => null, 'exporter' => 'memory', 'user' => null, 'password' => null, 'host' => 'default', 'port' => null, 'path' => null],
        ];
    }

    /**
     * @dataProvider invalidDsnProvider
     */
    public function testInvalidDsn(string $dsn, string $exceptionMessage): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);
        ExporterDsn::fromString($dsn);
    }

    /**
     * @return iterable<int, string[]>
     */
    public static function invalidDsnProvider(): iterable
    {
        yield [
            'some://',
            'The DSN is invalid.',
        ];

        yield [
            '//sendmail',
            'The DSN must contain a scheme.',
        ];

        yield [
            'file:///some/path',
            'The DSN must contain a host (use "default" by default).',
        ];
    }
}
