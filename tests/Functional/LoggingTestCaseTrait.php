<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional;

use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\SDK\Logs\Exporter\InMemoryExporter;
use OpenTelemetry\SDK\Logs\ReadableLogRecord;
use OpenTelemetry\SDK\Trace\SpanDataInterface;

trait LoggingTestCaseTrait
{
    protected static function getLogExporter(?string $exporterId = null): InMemoryExporter
    {
        $exporter = self::getContainer()->get($exporterId ?? 'open_telemetry.logs.exporters.in_memory');
        self::assertInstanceOf(InMemoryExporter::class, $exporter);

        return $exporter;
    }

    protected static function assertLogsCount(int $count, ?string $exporterId = null): void
    {
        self::assertCount($count, self::getLogExporter($exporterId)->getStorage());
    }

    /**
     * @return LogRecord[]
     */
    protected static function getLogs(?string $exporterId = null): array
    {
        return self::getLogExporter($exporterId)->getStorage()->getArrayCopy();
    }

    protected static function getLog(string $message, string $level, ?string $exporterId = null): ?ReadableLogRecord
    {
        $logs = self::getLogs($exporterId);

        self::assertContainsOnlyInstancesOf(ReadableLogRecord::class, $logs);

        $foundLog = null;
        foreach ($logs as $log) {
            if ($message === $log->getBody() && $level === $log->getSeverityText()) {
                $foundLog = $log;
                break;
            }
        }

        return $foundLog;
    }

    protected static function assertHasLog(string $message, string $level, ?string $exporterId = null): void
    {
        self::assertNotNull(self::getLog($message, $level, $exporterId));
    }

    protected static function assertLogHasSpanContext(ReadableLogRecord $log, SpanDataInterface $span): void
    {
        self::assertSame($log->getSpanContext()->getSpanId(), $span->getSpanId());
    }
}
