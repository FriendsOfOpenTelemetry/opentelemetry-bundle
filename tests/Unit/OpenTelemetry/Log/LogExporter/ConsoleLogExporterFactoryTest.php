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
    public function testCreateExporter(string $dsn, ExporterOptionsInterface $options, bool $supports): void
    {
        $dsn = ExporterDsn::fromString($dsn);
        $exporterFactory = new ConsoleLogExporterFactory(new TransportFactory([
            new StreamTransportFactory(),
        ]));
        self::assertEquals($supports, $exporterFactory->supports($dsn, $options));
        if (false === $supports) {
            return;
        }

        $exporter = $exporterFactory->createExporter($dsn, $options);

        $reflection = new \ReflectionObject($exporter);
        $transport = $reflection->getProperty('transport');

        self::assertInstanceOf(StreamTransport::class, $transport->getValue($exporter));
    }

    /**
     * @return \Generator<string, array{
     *     string,
     *     ExporterOptionsInterface,
     *     bool,
     * }>
     */
    public static function exporterProvider(): \Generator
    {
        yield 'stream+console' => [
            'stream+console://default',
            new EmptyExporterOptions(),
            true,
        ];

        yield 'stream+console_with-path' => [
            'stream+console://default/tmp/symfony.log',
            new EmptyExporterOptions(),
            true,
        ];

        yield 'unsupported' => [
            'foo://default',
            new EmptyExporterOptions(),
            false,
        ];
    }
}
