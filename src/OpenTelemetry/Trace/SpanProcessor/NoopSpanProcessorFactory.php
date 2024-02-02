<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor;

use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\NoopSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;

final class NoopSpanProcessorFactory extends AbstractSpanProcessorFactory
{
    public static function createProcessor(
        array $processors = [],
        ?SpanExporterInterface $exporter = null
    ): SpanProcessorInterface {
        return new NoopSpanProcessor();
    }
}
