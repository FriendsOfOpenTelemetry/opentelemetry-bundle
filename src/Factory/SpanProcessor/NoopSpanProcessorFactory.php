<?php

namespace GaelReyrol\OpenTelemetryBundle\Factory\SpanProcessor;

use OpenTelemetry\SDK\Trace\SpanProcessor\NoopSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;

final class NoopSpanProcessorFactory implements SpanProcessorFactoryInterface
{
    public static function createFromOptions(array $options): SpanProcessorInterface
    {
        return new NoopSpanProcessor();
    }
}
