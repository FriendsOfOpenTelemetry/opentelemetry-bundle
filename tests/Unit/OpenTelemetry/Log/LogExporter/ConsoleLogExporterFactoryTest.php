<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Log\LogExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\EmptyExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter\ConsoleLogExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\StreamTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportFactory;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConsoleLogExporterFactory::class)]
class ConsoleLogExporterFactoryTest extends TestCase
{
    #[DataProvider('exporterProvider')]
    public function testCreateExporter(string $dsn, ExporterOptionsInterface $options, ?\Exception $exception): void
    {
        if (null !== $exception) {
            self::expectExceptionObject($exception);
        }

        $exporter = (new ConsoleLogExporterFactory(new TransportFactory([
            new StreamTransportFactory(),
        ])))->createExporter(ExporterDsn::fromString($dsn), $options);

        $reflection = new \ReflectionObject($exporter);
        $transport = $reflection->getProperty('transport');

        self::assertInstanceOf(StreamTransport::class, $transport->getValue($exporter));
    }

    /**
     * @return \Generator<array{0: string, 1: ExporterOptionsInterface, 2: ?\Exception}>
     */
    public static function exporterProvider(): \Generator
    {
        yield [
            'stream+console://default',
            new EmptyExporterOptions(),
            null,
        ];

        yield [
            // This DSN is valid but given the context of the transport, the failure is expected.
            'stream+console://default/var/log/symfony.log',
            new EmptyExporterOptions(),
            new \ErrorException('fopen(/var/log/symfony.log): Failed to open stream: Permission denied'),
        ];

        yield [
            'stream+console://',
            new EmptyExporterOptions(),
            new \InvalidArgumentException('The DSN is invalid.'),
        ];

        yield [
            'in-memory://default',
            new EmptyExporterOptions(),
            new \InvalidArgumentException('No transport supports the given endpoint.'),
        ];

        yield [
            'http+otlp://default',
            new EmptyExporterOptions(),
            new \InvalidArgumentException('No transport supports the given endpoint.'),
        ];

        yield [
            'grpc+otlp://default',
            new EmptyExporterOptions(),
            new \InvalidArgumentException('No transport supports the given endpoint.'),
        ];

        yield [
            'noop://default',
            new EmptyExporterOptions(),
            new \InvalidArgumentException('No transport supports the given endpoint.'),
        ];
    }
}
