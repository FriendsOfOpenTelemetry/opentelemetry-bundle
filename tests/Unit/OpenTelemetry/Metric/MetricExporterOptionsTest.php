<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Metric;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterCompressionEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterFormatEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\MetricTemporalityEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\HeadersHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @phpstan-import-type ExporterOptions from ExporterOptionsInterface
 */
#[CoversClass(MetricExporterOptions::class)]
class MetricExporterOptionsTest extends TestCase
{
    public function testDefault(): void
    {
        $options = new MetricExporterOptions();

        self::assertSame(MetricTemporalityEnum::Delta, $options->getTemporality());

        $otlpOptions = $options->getOtlpOptions();
        self::assertSame(OtlpExporterFormatEnum::Json, $otlpOptions->getFormat());
        self::assertSame([
            'User-Agent' => sprintf('%s, Symfony OTEL Bundle', HeadersHelper::getOpenTelemetryUserAgentHeaderValue()),
        ], $otlpOptions->getHeaders());
        self::assertSame(OtlpExporterCompressionEnum::None, $otlpOptions->getCompression());
        self::assertSame(.10, $otlpOptions->getTimeout());
        self::assertSame(100, $otlpOptions->getRetryDelay());
        self::assertSame(3, $otlpOptions->getMaxRetries());
        self::assertNull($otlpOptions->getCaCertificate());
        self::assertNull($otlpOptions->getCertificate());
        self::assertNull($otlpOptions->getKey());
    }

    /**
     * @param array&ExporterOptions                 $configuration
     * @param callable(MetricExporterOptions): void $assertion
     */
    #[DataProvider('configurationProvider')]
    public function testFromConfiguration(array $configuration, callable $assertion): void
    {
        $options = MetricExporterOptions::fromConfiguration($configuration);

        $assertion($options);
    }

    /**
     * @return \Generator<array{
     *     0: ExporterOptions,
     *     1: callable(MetricExporterOptions): void
     * }>
     */
    public static function configurationProvider(): \Generator
    {
        yield [
            ['temporality' => 'cumulative'],
            function (MetricExporterOptions $options) {
                self::assertSame(MetricTemporalityEnum::Cumulative, $options->getTemporality());
            },
        ];

        yield [
            ['format' => 'ndjson'],
            function (MetricExporterOptions $options) {
                self::assertSame(OtlpExporterFormatEnum::Ndjson, $options->getOtlpOptions()->getFormat());
            },
        ];

        yield [
            ['headers' => ['X-Foo' => 'Bar']],
            fn (MetricExporterOptions $options) => self::assertSame([
                'X-Foo' => 'Bar',
                'User-Agent' => sprintf('%s, Symfony OTEL Bundle', HeadersHelper::getOpenTelemetryUserAgentHeaderValue()),
            ], $options->getOtlpOptions()->getHeaders()),
        ];

        yield [
            ['compression' => 'gzip'],
            fn (MetricExporterOptions $options) => self::assertSame(OtlpExporterCompressionEnum::Gzip, $options->getOtlpOptions()->getCompression()),
        ];

        yield [
            ['timeout' => 1.0],
            fn (MetricExporterOptions $options) => self::assertSame(1.0, $options->getOtlpOptions()->getTimeout()),
        ];

        yield [
            ['retry' => 300],
            fn (MetricExporterOptions $options) => self::assertSame(300, $options->getOtlpOptions()->getRetryDelay()),
        ];

        yield [
            ['max' => 5],
            fn (MetricExporterOptions $options) => self::assertSame(5, $options->getOtlpOptions()->getMaxRetries()),
        ];

        yield [
            ['ca' => 'CA'],
            fn (MetricExporterOptions $options) => self::assertSame('CA', $options->getOtlpOptions()->getCaCertificate()),
        ];

        yield [
            ['cert' => 'CERTIFICATE'],
            fn (MetricExporterOptions $options) => self::assertSame('CERTIFICATE', $options->getOtlpOptions()->getCertificate()),
        ];

        yield [
            ['key' => 'KEY'],
            fn (MetricExporterOptions $options) => self::assertSame('KEY', $options->getOtlpOptions()->getKey()),
        ];
    }
}
