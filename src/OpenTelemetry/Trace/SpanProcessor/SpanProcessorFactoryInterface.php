<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor;

use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;

interface SpanProcessorFactoryInterface
{
    /**
     * @param SpanProcessorInterface[] $processors
     */
    public static function create(
        array $processors = [],
        SpanExporterInterface $exporter = null
    ): SpanProcessorInterface;
}
