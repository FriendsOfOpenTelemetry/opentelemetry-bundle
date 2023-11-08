<?php

namespace GaelReyrol\OpenTelemetryBundle\Factory;

use OpenTelemetry\SDK\Trace\SpanExporterInterface;

interface SpanExporterFactoryInterface
{
    /**
     * @param array{
     *     endpoint: string,
     *     headers: array<string, string>,
     *     compression: string,
     *     format: string
     * } $options
     */
    public function createFromOptions(array $options): SpanExporterInterface;
}
