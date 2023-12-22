<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor;

use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\Processor\MultiLogRecordProcessor;
use OpenTelemetry\SDK\Logs\Processor\NoopLogRecordProcessor;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;

enum LogProcessorEnum: string
{
    // case Batch = 'batch';
    case Multi = 'multi';
    case Noop = 'noop';
    case Simple = 'simple';

    /**
     * @return class-string<LogProcessorFactoryInterface>
     */
    public function getFactoryClass(): string
    {
        return match ($this) {
            // self::Batch => BatchLogRecordProcessorFactory::class,
            self::Multi => MultiLogProcessorFactory::class,
            self::Noop => NoopLogProcessorFactory::class,
            self::Simple => SimpleLogProcessorFactory::class,
        };
    }

    /**
     * @return class-string<LogRecordProcessorInterface>
     */
    public function getClass(): string
    {
        return match ($this) {
            // self::Batch => BatchLogRecordProcessor::class,
            self::Multi => MultiLogRecordProcessor::class,
            self::Noop => NoopLogRecordProcessor::class,
            self::Simple => SimpleLogRecordProcessor::class,
        };
    }
}
