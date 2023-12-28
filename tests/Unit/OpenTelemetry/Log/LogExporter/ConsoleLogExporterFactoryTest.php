<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Log\LogExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\EmptyExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter\ConsoleLogExporterFactory;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransport;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter\ConsoleLogExporterFactory
 */
class ConsoleLogExporterFactoryTest extends TestCase
{
    /**
     * @dataProvider exporterProvider
     */
    public function testCreateExporter(string $dsn, ExporterOptionsInterface $options, ?\Exception $exception): void
    {
        if (null !== $exception) {
            self::expectExceptionObject($exception);
        }

        $exporter = ConsoleLogExporterFactory::createExporter(ExporterDsn::fromString($dsn), $options);

        $reflection = new \ReflectionObject($exporter);
        $transport = $reflection->getProperty('transport');

        self::assertInstanceOf(StreamTransport::class, $transport->getValue($exporter));
    }

    /**
     * @return \Generator<array{0: string, 1: ExporterOptionsInterface, 2: ?\Exception}>
     */
    public function exporterProvider(): \Generator
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
            new \InvalidArgumentException('Unsupported exporter endpoint or options for this transport.'),
        ];

        yield [
            'http+otlp://default',
            new EmptyExporterOptions(),
            new \InvalidArgumentException('Unsupported exporter endpoint or options for this transport.'),
        ];

        yield [
            'grpc+otlp://default',
            new EmptyExporterOptions(),
            new \InvalidArgumentException('Unsupported exporter endpoint or options for this transport.'),
        ];

        yield [
            'noop://default',
            new EmptyExporterOptions(),
            new \InvalidArgumentException('Unsupported exporter endpoint or options for this transport.'),
        ];
    }
}
