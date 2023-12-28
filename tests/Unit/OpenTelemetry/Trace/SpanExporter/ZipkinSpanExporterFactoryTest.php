<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Trace\SpanExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\ZipkinSpanExporterFactory;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransport;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\ZipkinSpanExporterFactory
 */
class ZipkinSpanExporterFactoryTest extends TestCase
{
    /**
     * @dataProvider exporterProvider
     *
     * @param ?class-string<TransportInterface<string>> $transportClass
     */
    public function testCreateExporter(
        string $dsn,
        ExporterOptionsInterface $options,
        ?string $transportClass,
        ?\Exception $exception,
    ): void {
        if (null !== $exception) {
            self::expectExceptionObject($exception);
        }

        $exporter = ZipkinSpanExporterFactory::createExporter(ExporterDsn::fromString($dsn), $options);

        $reflection = new \ReflectionObject($exporter);
        $transport = $reflection->getProperty('transport');

        self::assertInstanceOf(PsrTransport::class, $transport->getValue($exporter));
    }

    /**
     * @return \Generator<array{
     *     0: string,
     *     1: ExporterOptionsInterface,
     *     2: ?class-string<TransportInterface<string>>,
     *     3: ?\Exception,
     * }>
     */
    public function exporterProvider(): \Generator
    {
        yield [
            'http+zipkin://default',
            new OtlpExporterOptions(),
            null,
            null,
        ];
    }
}
