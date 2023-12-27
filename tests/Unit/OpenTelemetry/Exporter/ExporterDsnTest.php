<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Exporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn
 */
class ExporterDsnTest extends TestCase
{
    public function testGetOption(): void
    {
        $options = ['format' => 'json', 'nullable' => null];
        $dsn = new ExporterDsn(scheme: 'http+otlp', host: 'localhost', options: $options);

        self::assertSame('json', $dsn->getOption('format'));
        self::assertSame('default', $dsn->getOption('nullable', 'default'));
        self::assertSame('default', $dsn->getOption('not_existent_property', 'default'));
    }

    /**
     * @dataProvider fromStringProvider
     *
     * @param array{transport?: string, exporter?: string} $explodedScheme
     */
    public function testFromString(string $string, ExporterDsn $dsn, array $explodedScheme): void
    {
        self::assertEquals($dsn, ExporterDsn::fromString($string));
        self::assertEquals($explodedScheme, [
            'transport' => $dsn->getTransport(),
            'exporter' => $dsn->getExporter(),
        ]);
    }

    /**
     * @return iterable<string, array{0: string, 1: ExporterDsn, 2: array{transport: ?string, exporter: string}}>
     */
    public static function fromStringProvider(): iterable
    {
        yield 'gRPC Transport, OTLP Exporter' => [
            'grpc+otlp://localhost:4317',
            new ExporterDsn('grpc+otlp', 'localhost', null, null, 4317),
            ['transport' => 'grpc', 'exporter' => 'otlp'],
        ];
        yield 'HTTP Transport, OTLP Exporter with JSON Content-Type, Compression GZIP' => [
            'http+otlp://localhost:4318/v1/traces?content-type=application/json&compression=gzip',
            new ExporterDsn('http+otlp', 'localhost', null, null, 4318, '/v1/traces', [
                'content-type' => 'application/json',
                'compression' => 'gzip',
            ]),
            ['transport' => 'http', 'exporter' => 'otlp'],
        ];
        yield 'HTTP Transport, Zipkin Exporter with basic authentication' => [
            'http+zipkin://user:password@localhost:9411/api/v2/spans',
            new ExporterDsn('http+zipkin', 'localhost', 'user', 'password', 9411, '/api/v2/spans'),
            ['transport' => 'http', 'exporter' => 'zipkin'],
        ];
        yield 'Stream Transport, Console Exporter' => [
            'stream+console://default',
            new ExporterDsn('stream+console', 'default'),
            ['transport' => 'stream', 'exporter' => 'console'],
        ];
        yield 'Memory Exporter' => [
            'memory://default',
            new ExporterDsn('memory', 'default'),
            ['transport' => null, 'exporter' => 'memory'],
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
