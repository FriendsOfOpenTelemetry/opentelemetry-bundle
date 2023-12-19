<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Dsn;
use PHPUnit\Framework\TestCase;

class DsnTest extends TestCase
{
    public function testGetOption(): void
    {
        $options = ['format' => 'json', 'nullable' => null];
        $dsn = new Dsn(scheme: 'http+otlp', host: 'localhost', options: $options);

        self::assertSame('json', $dsn->getOption('format'));
        self::assertSame('default', $dsn->getOption('nullable', 'default'));
        self::assertSame('default', $dsn->getOption('not_existent_property', 'default'));
    }

    /**
     * @dataProvider fromStringProvider
     */
    public function testFromString(string $string, Dsn $dsn): void
    {
        self::assertEquals($dsn, Dsn::fromString($string));
    }

    /**
     * @return iterable<string, array{0: string, 1: Dsn}>
     */
    public static function fromStringProvider(): iterable
    {
        yield 'HTTP Transport, OTLP Exporter with JSON Content-Type, Compression GZIP' => [
            'http+otlp://localhost:4318/v1/traces?content-type=application/json&compression=gzip',
            new Dsn('http+otlp', 'localhost', null, null, 4318, '/v1/traces', [
                'content-type' => 'application/json',
                'compression' => 'gzip',
            ]),
        ];
    }

    /**
     * @dataProvider invalidDsnProvider
     */
    public function testInvalidDsn(string $dsn, string $exceptionMessage): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);
        Dsn::fromString($dsn);
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
