<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Exporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterCompressionEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterFormatEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\HeadersHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterOptions
 *
 * @phpstan-import-type ExporterOptions from ExporterOptionsInterface
 */
#[CoversClass(OtlpExporterOptions::class)]
class OtlpExporterOptionsTest extends TestCase
{
    public function testDefault(): void
    {
        $options = new OtlpExporterOptions();

        self::assertSame(OtlpExporterFormatEnum::Json, $options->getFormat());
        self::assertSame([
            'User-Agent' => sprintf('%s, Symfony OTEL Bundle', HeadersHelper::getOpenTelemetryUserAgentHeaderValue()),
        ], $options->getHeaders());
        self::assertSame(OtlpExporterCompressionEnum::None, $options->getCompression());
        self::assertSame(.10, $options->getTimeout());
        self::assertSame(100, $options->getRetryDelay());
        self::assertSame(3, $options->getMaxRetries());
        self::assertNull($options->getCaCertificate());
        self::assertNull($options->getCertificate());
        self::assertNull($options->getKey());
    }

    /**
     * @param array&ExporterOptions               $configuration
     * @param callable(OtlpExporterOptions): void $assertion
     */
    #[DataProvider('configurationProvider')]
    public function testFromConfiguration(array $configuration, callable $assertion): void
    {
        $options = OtlpExporterOptions::fromConfiguration($configuration);

        $assertion($options);
    }

    /**
     * @return \Generator<array{
     *     0: ExporterOptions,
     *     1: callable(OtlpExporterOptions): void
     * }>
     */
    public static function configurationProvider(): \Generator
    {
        yield [
            ['format' => 'ndjson'],
            function (OtlpExporterOptions $options) {
                self::assertSame(OtlpExporterFormatEnum::Ndjson, $options->getFormat());
            },
        ];

        yield [
            ['headers' => ['X-Foo' => 'Bar']],
            fn (OtlpExporterOptions $options) => self::assertSame([
                'X-Foo' => 'Bar',
                'User-Agent' => sprintf('%s, Symfony OTEL Bundle', HeadersHelper::getOpenTelemetryUserAgentHeaderValue()),
            ], $options->getHeaders()),
        ];

        yield [
            ['compression' => 'gzip'],
            fn (OtlpExporterOptions $options) => self::assertSame(OtlpExporterCompressionEnum::Gzip, $options->getCompression()),
        ];

        yield [
            ['timeout' => 1.0],
            fn (OtlpExporterOptions $options) => self::assertSame(1.0, $options->getTimeout()),
        ];

        yield [
            ['retry' => 300],
            fn (OtlpExporterOptions $options) => self::assertSame(300, $options->getRetryDelay()),
        ];

        yield [
            ['max' => 5],
            fn (OtlpExporterOptions $options) => self::assertSame(5, $options->getMaxRetries()),
        ];

        yield [
            ['ca' => 'CA'],
            fn (OtlpExporterOptions $options) => self::assertSame('CA', $options->getCaCertificate()),
        ];

        yield [
            ['cert' => 'CERTIFICATE'],
            fn (OtlpExporterOptions $options) => self::assertSame('CERTIFICATE', $options->getCertificate()),
        ];

        yield [
            ['key' => 'KEY'],
            fn (OtlpExporterOptions $options) => self::assertSame('KEY', $options->getKey()),
        ];
    }
}
