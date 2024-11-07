<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Transport;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ConsoleExporterEndpoint;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\EmptyExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterEndpointInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterEndpoint;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporterEndpoint;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporterEndpoint;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TraceExporterEndpoint;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\ZipkinExporterEndpoint;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\GrpcTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\OtlpHttpTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\PsrHttpTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\StreamTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportFactory;
use OpenTelemetry\Contrib\Grpc\GrpcTransport;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransport;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransport;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(TransportFactory::class)]
class TransportFactoryTest extends TestCase
{
    /**
     * @param array<class-string<ExporterEndpointInterface>, bool> $supportedEndpoints
     */
    #[DataProvider('transportProvider')]
    public function testCreateTransport(
        ExporterDsn $dsn,
        ExporterOptionsInterface $options,
        array $supportedEndpoints,
        ?string $expectedTransportClass,
    ): void {
        $factory = new TransportFactory([
            new GrpcTransportFactory(),
            new OtlpHttpTransportFactory(),
            new PsrHttpTransportFactory(),
            new StreamTransportFactory(),
        ]);

        foreach ($supportedEndpoints as $endpointClass => $supported) {
            if (true === $supported) {
                $endpoint = $endpointClass::fromDsn($dsn);
                self::assertTrue($factory->supports($endpoint, $options));
                $transport = $factory->createTransport($endpoint, $options);
                self::assertInstanceOf($expectedTransportClass, $transport);
            } else {
                self::expectExceptionMessageMatches('#Unsupported .*#');
                $endpoint = $endpointClass::fromDsn($dsn);
                self::assertFalse($factory->supports($endpoint, $options));
            }
        }
    }

    /**
     * @return \Generator<array{
     *     0: ExporterDsn,
     *     1: ExporterOptionsInterface,
     *     2: array<class-string<ExporterEndpointInterface>, bool>,
     *     3: ?class-string<TransportInterface<string>>,
     * }>
     */
    public static function transportProvider(): \Generator
    {
        yield [
            ExporterDsn::fromString('stream+console://default'),
            new EmptyExporterOptions(),
            [
                LogExporterEndpoint::class => true,
                MetricExporterEndpoint::class => true,
                TraceExporterEndpoint::class => true,
                ZipkinExporterEndpoint::class => false,
                ConsoleExporterEndpoint::class => true,
                OtlpExporterEndpoint::class => false,
            ],
            StreamTransport::class,
        ];

        yield [
            ExporterDsn::fromString('http+zipkin://default'),
            new EmptyExporterOptions(),
            [
                LogExporterEndpoint::class => false,
                MetricExporterEndpoint::class => false,
                TraceExporterEndpoint::class => true,
                ZipkinExporterEndpoint::class => true,
                ConsoleExporterEndpoint::class => false,
                OtlpExporterEndpoint::class => false,
            ],
            PsrTransport::class,
        ];

        yield [
            ExporterDsn::fromString('http+otlp://default'),
            new EmptyExporterOptions(),
            [
                LogExporterEndpoint::class => true,
                MetricExporterEndpoint::class => true,
                TraceExporterEndpoint::class => true,
                ZipkinExporterEndpoint::class => false,
                ConsoleExporterEndpoint::class => false,
                OtlpExporterEndpoint::class => true,
            ],
            PsrTransport::class,
        ];

        yield [
            ExporterDsn::fromString('grpc+otlp://default'),
            new EmptyExporterOptions(),
            [
                LogExporterEndpoint::class => true,
                MetricExporterEndpoint::class => true,
                TraceExporterEndpoint::class => true,
                ZipkinExporterEndpoint::class => false,
                ConsoleExporterEndpoint::class => false,
                OtlpExporterEndpoint::class => true,
            ],
            GrpcTransport::class,
        ];

        yield [
            ExporterDsn::fromString('noop://default'),
            new EmptyExporterOptions(),
            [
                LogExporterEndpoint::class => false,
                MetricExporterEndpoint::class => false,
                TraceExporterEndpoint::class => false,
                ZipkinExporterEndpoint::class => false,
                ConsoleExporterEndpoint::class => false,
                OtlpExporterEndpoint::class => false,
            ],
            null,
        ];
    }
}
