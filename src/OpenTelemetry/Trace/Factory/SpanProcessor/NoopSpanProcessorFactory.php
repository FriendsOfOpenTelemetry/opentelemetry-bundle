<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Trace\Factory\SpanProcessor;

use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\NoopSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;

final class NoopSpanProcessorFactory implements SpanProcessorFactoryInterface
{
    public static function create(
        array $processors = [],
        SpanExporterInterface $exporter = null
    ): SpanProcessorInterface {
        return new NoopSpanProcessor();
    }
}
