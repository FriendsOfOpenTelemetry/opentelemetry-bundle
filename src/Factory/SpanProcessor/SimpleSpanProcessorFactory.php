<?php

namespace GaelReyrol\OpenTelemetryBundle\Factory\SpanProcessor;

use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;

final class SimpleSpanProcessorFactory implements SpanProcessorFactoryInterface
{
    public static function createFromOptions(array $options): SpanProcessorInterface
    {
        return new SimpleSpanProcessor($options['exporter']);
    }
}
