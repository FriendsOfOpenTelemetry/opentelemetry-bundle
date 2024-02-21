<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Log\LogExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\EmptyExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter\ConsoleLogExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter\InMemoryLogExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter\LogExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter\NoopLogExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter\OtlpLogExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\GrpcTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\OtlpHttpTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\PsrHttpTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\StreamTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportFactory;
use OpenTelemetry\Contrib\Grpc\GrpcTransport;
use OpenTelemetry\Contrib\Otlp\LogsExporter;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransport;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransport;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Logs\Exporter\ConsoleExporter;
use OpenTelemetry\SDK\Logs\Exporter\InMemoryExporter;
use OpenTelemetry\SDK\Logs\Exporter\NoopExporter;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(LogExporterFactory::class)]
class LogExporterFactoryTest extends TestCase
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

    private function getExporterFactory(): LogExporterFactory
    {
        return new LogExporterFactory([
            new ConsoleLogExporterFactory($this->getTransportFactory()),
            new InMemoryLogExporterFactory($this->getTransportFactory()),
            new NoopLogExporterFactory($this->getTransportFactory()),
            new OtlpLogExporterFactory($this->getTransportFactory()),
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
     *     class-string<LogRecordExporterInterface>,
     *     ?class-string<TransportInterface<string>>,
     * }>
     */
    public static function exporterProvider(): \Generator
    {
        yield 'stream+console' => [
            'stream+console://default',
            new EmptyExporterOptions(),
            ConsoleExporter::class,
            StreamTransport::class,
        ];

        yield 'in-memory' => [
            'in-memory://default',
            new EmptyExporterOptions(),
            InMemoryExporter::class,
            null,
        ];

        yield 'noop' => [
            'noop://default',
            new EmptyExporterOptions(),
            NoopExporter::class,
            null,
        ];

        yield 'http+otlp' => [
            'http+otlp://default',
            new OtlpExporterOptions(),
            LogsExporter::class,
            PsrTransport::class,
        ];

        yield 'grpc+otlp' => [
            'grpc+otlp://default',
            new OtlpExporterOptions(),
            LogsExporter::class,
            GrpcTransport::class,
        ];
    }

    public function testUnsupportedDsn(): void
    {
        $exporterFactory = $this->getExporterFactory();

        $dsn = ExporterDsn::fromString('foo://bar');
        $options = new OtlpExporterOptions();

        self::assertFalse($exporterFactory->supports($dsn, $options));

        self::expectExceptionObject(new \InvalidArgumentException('No Log exporter supports the given DSN.'));
        $exporterFactory->createExporter($dsn, $options);
    }
}
