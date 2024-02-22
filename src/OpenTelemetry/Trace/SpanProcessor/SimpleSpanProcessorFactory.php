<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor;

use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;

final class SimpleSpanProcessorFactory extends AbstractSpanProcessorFactory
{
    public function createProcessor(
        array $processors = [],
        ?SpanExporterInterface $exporter = null
    ): SpanProcessorInterface {
        if (null === $exporter) {
            throw new \InvalidArgumentException('Exporter is null');
        }

        return new SimpleSpanProcessor($exporter);
    }
}
