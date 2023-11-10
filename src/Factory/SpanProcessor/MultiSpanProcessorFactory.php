<?php

namespace GaelReyrol\OpenTelemetryBundle\Factory\SpanProcessor;

use OpenTelemetry\SDK\Trace\SpanProcessor\MultiSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;

final class MultiSpanProcessorFactory implements SpanProcessorFactoryInterface
{
    public static function createFromOptions(array $options): SpanProcessorInterface
    {
        return new MultiSpanProcessor(...$options['processors']);
    }
}
