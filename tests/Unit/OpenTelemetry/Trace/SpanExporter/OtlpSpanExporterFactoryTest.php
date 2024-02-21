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
        ?\Exception $exception,
    ): void {
        if (null !== $exception) {
            self::expectExceptionObject($exception);
        }

        $exporter = (new OtlpSpanExporterFactory(new TransportFactory([
            new GrpcTransportFactory(),
            new OtlpHttpTransportFactory(),
        ])))->createExporter(ExporterDsn::fromString($dsn), $options);

        $reflection = new \ReflectionObject($exporter);
        $transport = $reflection->getProperty('transport');

        self::assertInstanceOf($transportClass, $transport->getValue($exporter));
    }

    /**
     * @return \Generator<array{
     *     0: string,
     *     1: ExporterOptionsInterface,
     *     2: ?class-string<TransportInterface<string>>,
     *     3: ?\Exception,
     * }>
     */
    public static function exporterProvider(): \Generator
    {
        yield [
            'http+otlp://default',
            new OtlpExporterOptions(),
            PsrTransport::class,
            null,
        ];

        yield [
            'grpc+otlp://default',
            new OtlpExporterOptions(),
            GrpcTransport::class,
            null,
        ];

        yield [
            'stream+console://default',
            new OtlpExporterOptions(),
            null,
            new \InvalidArgumentException('No transport supports the given endpoint.'),
        ];

        yield [
            'http+zipkin://default',
            new OtlpExporterOptions(),
            null,
            new \InvalidArgumentException('No transport supports the given endpoint.'),
        ];

        yield [
            'in-memory://default',
            new OtlpExporterOptions(),
            null,
            new \InvalidArgumentException('No transport supports the given endpoint.'),
        ];

        yield [
            'noop://default',
            new OtlpExporterOptions(),
            null,
            new \InvalidArgumentException('Unsupported DSN for Trace exporter'),
        ];
    }
}
