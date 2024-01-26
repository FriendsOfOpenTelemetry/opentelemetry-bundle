<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Log\LogExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter\OtlpLogExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\GrpcTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\PsrHttpTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportFactory;
use OpenTelemetry\Contrib\Grpc\GrpcTransport;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransport;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(OtlpLogExporterFactory::class)]
class OtlpLogExporterFactoryTest extends TestCase
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

        $exporter = (new OtlpLogExporterFactory(new TransportFactory([
            new GrpcTransportFactory(),
            new PsrHttpTransportFactory(),
        ])))
            ->createExporter(ExporterDsn::fromString($dsn), $options);

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
            new \InvalidArgumentException('DSN exporter must be of type Otlp.'),
        ];

        yield [
            'http+zipkin://default',
            new OtlpExporterOptions(),
            null,
            new \InvalidArgumentException('Unsupported DSN exporter.'),
        ];

        yield [
            'in-memory://default',
            new OtlpExporterOptions(),
            null,
            new \InvalidArgumentException('DSN exporter must be of type Otlp.'),
        ];

        yield [
            'noop://default',
            new OtlpExporterOptions(),
            null,
            new \InvalidArgumentException('DSN exporter must be of type Otlp.'),
        ];
    }
}
