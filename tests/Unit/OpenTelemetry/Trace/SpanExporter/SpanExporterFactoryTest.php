<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Trace\SpanExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\EmptyExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\ConsoleSpanExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\InMemorySpanExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\OtlpSpanExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\SpanExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\ZipkinSpanExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\GrpcTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\OtlpHttpTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\PsrHttpTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\StreamTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportFactory;
use OpenTelemetry\Contrib\Grpc\GrpcTransport;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinExporter;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransport;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporter;
use OpenTelemetry\SDK\Trace\SpanExporter\InMemoryExporter;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SpanExporterFactory::class)]
class SpanExporterFactoryTest extends TestCase
{
    private function getTransportFactory(): TransportFactory
    {
        return new TransportFactory([
            new GrpcTransportFactory(),
            new OtlpHttpTransportFactory(),
            new PsrHttpTransportFactory(),
            new StreamTransportFactory(),
        ]);
    }

    #[DataProvider('exporterProvider')]
    public function testCreateExporter(
        string $dsn,
        ExporterOptionsInterface $options,
        ?string $exporterClass,
        ?string $transportClass,
    ): void {
        $exporterFactory = (new SpanExporterFactory([
            new ConsoleSpanExporterFactory($this->getTransportFactory()),
            new InMemorySpanExporterFactory($this->getTransportFactory()),
            new OtlpSpanExporterFactory($this->getTransportFactory()),
            new ZipkinSpanExporterFactory($this->getTransportFactory()),
        ]));

        $exporter = $exporterFactory->createExporter(ExporterDsn::fromString($dsn), $options);

        self::assertInstanceOf($exporterClass, $exporter);

        if (null !== $transportClass) {
            $reflection = new \ReflectionObject($exporter);
            $transport = $reflection->getProperty('transport');

            self::assertInstanceOf($transportClass, $transport->getValue($exporter));
        }
    }

    /**
     * @return \Generator<array{
     *     0: string,
     *     1: ExporterOptionsInterface,
     *     2: class-string<SpanExporterInterface>,
     *     3: ?class-string<TransportInterface<string>>,
     * }>
     */
    public static function exporterProvider(): \Generator
    {
        yield [
            'stream+console://default',
            new EmptyExporterOptions(),
            ConsoleSpanExporter::class,
            // This exporter has no transport
            null,
        ];

        yield [
            'in-memory://default',
            new EmptyExporterOptions(),
            InMemoryExporter::class,
            null,
        ];

        yield [
            'http+otlp://default',
            new OtlpExporterOptions(),
            SpanExporter::class,
            PsrTransport::class,
        ];

        yield [
            'grpc+otlp://default',
            new OtlpExporterOptions(),
            SpanExporter::class,
            GrpcTransport::class,
        ];

        yield [
            'http+zipkin://default',
            new EmptyExporterOptions(),
            ZipkinExporter::class,
            PsrTransport::class,
        ];
    }
}
