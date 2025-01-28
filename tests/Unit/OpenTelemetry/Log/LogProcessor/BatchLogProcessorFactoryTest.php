<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Log\LogProcessor;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\EmptyExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter\NoopLogExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor\BatchLogProcessorFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportFactory;
use OpenTelemetry\API\Common\Time\ClockInterface;
use OpenTelemetry\API\Common\Time\TestClock;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\Processor\BatchLogRecordProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(BatchLogProcessorFactory::class)]
class BatchLogProcessorFactoryTest extends TestCase
{
    /**
     * @param LogRecordProcessorInterface[] $processors
     * @param ?array{
     *      clock: ClockInterface,
     *      max_queue_size: int,
     *      schedule_delay: int,
     *      export_timeout: int,
     *      max_export_batch_size: int,
     *      auto_flush: bool,
     *      meter_provider: ?MeterProviderInterface,
     * } $batch
     */
    #[DataProvider('params')]
    public function testCreateProcessor(array $processors, ?array $batch, ?LogRecordExporterInterface $exporter, ?\Exception $exception): void
    {
        if ($exception instanceof \Exception) {
            self::expectExceptionObject($exception);
        } else {
            self::expectNotToPerformAssertions();
        }

        (new BatchLogProcessorFactory())->createProcessor(
            $processors,
            $batch,
            $exporter,
        );
    }

    /**
     * @return \Generator<string, array{
     *     0: array<mixed>,
     *     1: ?array{
     *       clock: ClockInterface,
     *       max_queue_size: int,
     *       schedule_delay: int,
     *       export_timeout: int,
     *       max_export_batch_size: int,
     *       auto_flush: bool,
     *       meter_provider: ?MeterProviderInterface,
     *     },
     *     2: ?LogRecordExporterInterface,
     *     3: ?\Exception
     * }>
     */
    public static function params(): \Generator
    {
        $noopExporter = (new NoopLogExporterFactory(new TransportFactory([])))
            ->createExporter(
                ExporterDsn::fromString('null://default'),
                EmptyExporterOptions::fromConfiguration([]),
            );

        yield 'ok' => [
            [],
            [
                'clock' => new TestClock(),
                'max_queue_size' => BatchLogRecordProcessor::DEFAULT_MAX_QUEUE_SIZE,
                'schedule_delay' => BatchLogRecordProcessor::DEFAULT_SCHEDULE_DELAY,
                'export_timeout' => BatchLogRecordProcessor::DEFAULT_EXPORT_TIMEOUT,
                'max_export_batch_size' => BatchLogRecordProcessor::DEFAULT_MAX_EXPORT_BATCH_SIZE,
                'auto_flush' => true,
                'meter_provider' => null,
            ],
            $noopExporter,
            null,
        ];

        yield 'exported required' => [
            [],
            null,
            null,
            new \InvalidArgumentException('You must provide an exporter when using a batch log processor'),
        ];

        yield 'batch required' => [
            [],
            null,
            $noopExporter,
            new \InvalidArgumentException('You must provide a batch configuration when using a batch log processor'),
        ];
    }
}
