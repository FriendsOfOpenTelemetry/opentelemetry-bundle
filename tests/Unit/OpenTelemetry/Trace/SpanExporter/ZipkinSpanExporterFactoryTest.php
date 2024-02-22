<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Trace\SpanExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\ZipkinSpanExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\PsrHttpTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportFactory;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransport;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ZipkinSpanExporterFactory::class)]
class ZipkinSpanExporterFactoryTest extends TestCase
{
    /**
     * @param ?class-string<TransportInterface<string>> $transportClass
     */
    #[DataProvider('exporterProvider')]
    public function testCreateExporter(
        string $dsn,
        ExporterOptionsInterface $options,
        ?string $transportClass,
        bool $supports,
    ): void {
        $dsn = ExporterDsn::fromString($dsn);
        $exporterFactory = new ZipkinSpanExporterFactory(new TransportFactory([
            new PsrHttpTransportFactory(),
        ]));

        self::assertEquals($supports, $exporterFactory->supports($dsn, $options));
        if (false === $supports) {
            return;
        }

        $exporter = $exporterFactory->createExporter($dsn, $options);

        $reflection = new \ReflectionObject($exporter);
        $transport = $reflection->getProperty('transport');

        self::assertInstanceOf(PsrTransport::class, $transport->getValue($exporter));
    }

    /**
     * @return \Generator<string, array{
     *     string,
     *     ExporterOptionsInterface,
     *     ?class-string<TransportInterface<string>>,
     *     bool,
     * }>
     */
    public static function exporterProvider(): \Generator
    {
        yield 'http+zipkin' => [
            'http+zipkin://default',
            new OtlpExporterOptions(),
            null,
            true,
        ];

        yield 'unsupported' => [
            'foo://default',
            new OtlpExporterOptions(),
            null,
            false,
        ];
    }
}
