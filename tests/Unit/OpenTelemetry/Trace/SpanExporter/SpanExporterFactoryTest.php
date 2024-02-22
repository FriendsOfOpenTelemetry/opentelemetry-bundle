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

    private function getExporterFactory(): SpanExporterFactory
    {
        return new SpanExporterFactory([
            new ConsoleSpanExporterFactory($this->getTransportFactory()),
            new InMemorySpanExporterFactory($this->getTransportFactory()),
            new OtlpSpanExporterFactory($this->getTransportFactory()),
            new ZipkinSpanExporterFactory($this->getTransportFactory()),
        ]);
    }

    #[DataProvider('exporterProvider')]
    public function testCreateExporter(
        string $dsn,
        ExporterOptionsInterface $options,
        ?string $exporterClass,
        ?string $transportClass,
    ): void {
        $dsn = ExporterDsn::fromString($dsn);
        $exporterFactory = $this->getExporterFactory();

        self::assertTrue($exporterFactory->supports($dsn, $options));
        $exporter = $exporterFactory->createExporter($dsn, $options);

        self::assertInstanceOf($exporterClass, $exporter);

        if (null !== $transportClass) {
            $reflection = new \ReflectionObject($exporter);
            $transport = $reflection->getProperty('transport');

            self::assertInstanceOf($transportClass, $transport->getValue($exporter));
        }
    }

    /**
     * @return \Generator<string, array{
     *     string,
     *     ExporterOptionsInterface,
     *     class-string<SpanExporterInterface>,
     *     ?class-string<TransportInterface<string>>,
     * }>
     */
    public static function exporterProvider(): \Generator
    {
        yield 'stream+console' => [
            'stream+console://default',
            new EmptyExporterOptions(),
            ConsoleSpanExporter::class,
            // This exporter has no transport
            null,
        ];

        yield 'in-memory' => [
            'in-memory://default',
            new EmptyExporterOptions(),
            InMemoryExporter::class,
            null,
        ];

        yield 'http+otlp' => [
            'http+otlp://default',
            new OtlpExporterOptions(),
            SpanExporter::class,
            PsrTransport::class,
        ];

        yield 'grpc+otlp' => [
            'grpc+otlp://default',
            new OtlpExporterOptions(),
            SpanExporter::class,
            GrpcTransport::class,
        ];

        yield 'http+zipkin' => [
            'http+zipkin://default',
            new EmptyExporterOptions(),
            ZipkinExporter::class,
            PsrTransport::class,
        ];
    }

    public function testUnsupportedDsn(): void
    {
        $exporterFactory = $this->getExporterFactory();

        $dsn = ExporterDsn::fromString('foo://bar');
        $options = new OtlpExporterOptions();

        self::assertFalse($exporterFactory->supports($dsn, $options));

        self::expectExceptionObject(new \InvalidArgumentException('No span exporter supports the given DSN.'));
        $exporterFactory->createExporter($dsn, $options);
    }
}
