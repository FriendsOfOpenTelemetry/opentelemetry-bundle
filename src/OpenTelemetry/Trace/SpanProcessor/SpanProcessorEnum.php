<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor;

use OpenTelemetry\SDK\Trace\SpanProcessor\MultiSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\NoopSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;

enum SpanProcessorEnum: string
{
    //    case Batch = 'batch';
    case Multi = 'multi';
    case Simple = 'simple';
    case Noop = 'noop';

    /**
     * @return class-string<SpanProcessorFactoryInterface>
     */
    public function getFactoryClass(): string
    {
        return match ($this) {
            // self::Batch => BatchSpanProcessorFactory::class,
            self::Multi => MultiSpanProcessorFactory::class,
            self::Noop => NoopSpanProcessorFactory::class,
            self::Simple => SimpleSpanProcessorFactory::class,
        };
    }

    /**
     * @return class-string<SpanProcessorInterface>
     */
    public function getClass(): string
    {
        return match ($this) {
            // self::Batch => BatchSpanProcessor::class,
            self::Multi => MultiSpanProcessor::class,
            self::Noop => NoopSpanProcessor::class,
            self::Simple => SimpleSpanProcessor::class,
        };
    }
}
