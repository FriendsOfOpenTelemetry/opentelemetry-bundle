<?php

namespace GaelReyrol\OpenTelemetryBundle\Factory\SpanProcessor;

use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;

interface SpanProcessorFactoryInterface
{
    /**
     * @param array{
     *      processors?: SpanProcessorInterface[],
     *      exporter?: SpanExporterInterface,
     *  } $options
     */
    public static function createFromOptions(array $options): SpanProcessorInterface;
}
