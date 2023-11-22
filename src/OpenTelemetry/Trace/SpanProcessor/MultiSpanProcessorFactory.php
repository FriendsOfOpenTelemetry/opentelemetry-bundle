<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor;

use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\MultiSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;

final class MultiSpanProcessorFactory implements SpanProcessorFactoryInterface
{
    public static function create(
        array $processors = null,
        SpanExporterInterface $exporter = null
    ): SpanProcessorInterface {
        if (null === $processors || 0 === count($processors)) {
            throw new \InvalidArgumentException('Processors should not be empty');
        }

        return new MultiSpanProcessor(...$processors);
    }
}
