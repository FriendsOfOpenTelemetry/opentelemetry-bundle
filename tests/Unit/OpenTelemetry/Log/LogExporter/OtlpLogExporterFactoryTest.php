<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Log\LogExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter\OtlpLogExporterFactory;
use OpenTelemetry\Contrib\Grpc\GrpcTransport;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransport;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter\OtlpLogExporterFactory
 */
class OtlpLogExporterFactoryTest extends TestCase
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

        $exporter = OtlpLogExporterFactory::createExporter(ExporterDsn::fromString($dsn), $options);

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
    public function exporterProvider(): \Generator
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
