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

    #[DataProvider('exporterProvider')]
    public function testCreateExporter(
        string $dsn,
        ExporterOptionsInterface $options,
        ?string $exporterClass,
        ?string $transportClass,
    ): void {
        $exporterFactory = (new LogExporterFactory([
            new ConsoleLogExporterFactory($this->getTransportFactory()),
            new InMemoryLogExporterFactory($this->getTransportFactory()),
            new NoopLogExporterFactory($this->getTransportFactory()),
            new OtlpLogExporterFactory($this->getTransportFactory()),
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
     *     2: class-string<LogRecordExporterInterface>,
     *     3: ?class-string<TransportInterface<string>>,
     * }>
     */
    public static function exporterProvider(): \Generator
    {
        yield [
            'stream+console://default',
            new EmptyExporterOptions(),
            ConsoleExporter::class,
            StreamTransport::class,
        ];

        yield [
            'in-memory://default',
            new EmptyExporterOptions(),
            InMemoryExporter::class,
            null,
        ];

        yield [
            'noop://default',
            new EmptyExporterOptions(),
            NoopExporter::class,
            null,
        ];

        yield [
            'http+otlp://default',
            new OtlpExporterOptions(),
            LogsExporter::class,
            PsrTransport::class,
        ];

        yield [
            'grpc+otlp://default',
            new OtlpExporterOptions(),
            LogsExporter::class,
            GrpcTransport::class,
        ];
    }
}
