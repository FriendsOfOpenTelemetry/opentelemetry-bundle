<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Transport;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterCompressionEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterFormatEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportParams;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\HeadersHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(TransportParams::class)]
class TransportParamsTest extends TestCase
{
    public function testCreateDefault(): void
    {
        $params = new TransportParams();

        self::assertSame('application/json', $params->contentType);
        self::assertSame([], $params->headers);
        self::assertSame('none', $params->compression);
        self::assertSame(.10, $params->timeout);
        self::assertSame(100, $params->retryDelay);
        self::assertSame(3, $params->maxRetries);
        self::assertNull($params->caCert);
        self::assertNull($params->cert);
        self::assertNull($params->key);
    }

    /**
     * @param array{
     *     contentType: string,
     *     headers: array<string, mixed>,
     *     compression: string,
     *     timeout: float,
     *     retryDelay: int,
     *     maxRetries: int,
     *     caCert: ?string,
     *     cert: ?string,
     *     key: ?string,
     * } $expected
     */
    #[DataProvider('otlpExporterOptionProvider')]
    public function testFromOtlpExporterOptions(OtlpExporterOptions $options, array $expected): void
    {
        $params = $options->toTransportParams();

        self::assertSame($expected['contentType'], $params->contentType);
        self::assertSame($expected['headers'], $params->headers);
        self::assertSame($expected['compression'], $params->compression);
        self::assertSame($expected['timeout'], $params->timeout);
        self::assertSame($expected['retryDelay'], $params->retryDelay);
        self::assertSame($expected['maxRetries'], $params->maxRetries);
        self::assertSame($expected['caCert'], $params->caCert);
        self::assertSame($expected['cert'], $params->cert);
        self::assertSame($expected['key'], $params->key);
    }

    /**
     * @return \Generator<array{
     *     0: OtlpExporterOptions,
     *     1: array{
     *         contentType: string,
     *         headers: array<string, mixed>,
     *         compression: string,
     *         timeout: float,
     *         retryDelay: int,
     *         maxRetries: int,
     *         caCert: ?string,
     *         cert: ?string,
     *         key: ?string,
     *     }
     * }>
     */
    public static function otlpExporterOptionProvider(): \Generator
    {
        yield [
            new OtlpExporterOptions(),
            [
                'contentType' => 'application/json',
                'headers' => ['User-Agent' => sprintf('%s, Symfony OTEL Bundle', HeadersHelper::getOpenTelemetryUserAgentHeaderValue())],
                'compression' => 'none',
                'timeout' => .10,
                'retryDelay' => 100,
                'maxRetries' => 3,
                'caCert' => null,
                'cert' => null,
                'key' => null,
            ],
        ];

        yield [
            new OtlpExporterOptions(
                OtlpExporterFormatEnum::Ndjson,
                ['X-Foo' => 'Bar'],
                OtlpExporterCompressionEnum::Gzip,
                1.0,
                300,
                5,
                'CACERTIFICATE',
                'CERTIFICATE',
                'KEY',
            ),
            [
                'contentType' => 'application/x-ndjson',
                'headers' => [
                    'X-Foo' => 'Bar',
                    'User-Agent' => sprintf('%s, Symfony OTEL Bundle', HeadersHelper::getOpenTelemetryUserAgentHeaderValue()),
                ],
                'compression' => 'gzip',
                'timeout' => 1.0,
                'retryDelay' => 300,
                'maxRetries' => 5,
                'caCert' => 'CACERTIFICATE',
                'cert' => 'CERTIFICATE',
                'key' => 'KEY',
            ],
        ];
    }
}
