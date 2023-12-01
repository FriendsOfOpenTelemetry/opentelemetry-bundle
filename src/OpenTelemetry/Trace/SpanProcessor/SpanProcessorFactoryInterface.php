<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor;

use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;

interface SpanProcessorFactoryInterface
{
    /**
     * @param SpanProcessorInterface[] $processors
     */
    public static function create(
        array $processors = null,
        SpanExporterInterface $exporter = null
    ): SpanProcessorInterface;
}
