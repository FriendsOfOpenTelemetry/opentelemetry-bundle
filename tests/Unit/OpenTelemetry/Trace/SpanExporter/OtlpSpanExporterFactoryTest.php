<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Trace\SpanExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\OtlpSpanExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\GrpcTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\OtlpHttpTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportFactory;
use OpenTelemetry\Contrib\Grpc\GrpcTransport;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransport;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(OtlpSpanExporterFactory::class)]
class OtlpSpanExporterFactoryTest extends TestCase
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
        $exporterFactory = new OtlpSpanExporterFactory(new TransportFactory([
            new GrpcTransportFactory(),
            new OtlpHttpTransportFactory(),
        ]));

        self::assertEquals($supports, $exporterFactory->supports($dsn, $options));
        if (false === $supports) {
            return;
        }

        $exporter = $exporterFactory->createExporter($dsn, $options);

        $reflection = new \ReflectionObject($exporter);
        $transport = $reflection->getProperty('transport');

        self::assertInstanceOf($transportClass, $transport->getValue($exporter));
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
        yield 'http+otlp' => [
            'http+otlp://default',
            new OtlpExporterOptions(),
            PsrTransport::class,
            true,
        ];

        yield 'grpc+otlp' => [
            'grpc+otlp://default',
            new OtlpExporterOptions(),
            GrpcTransport::class,
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
