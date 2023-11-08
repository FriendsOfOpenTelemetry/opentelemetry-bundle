<?php

namespace GaelReyrol\OpenTelemetryBundle\Factory;

use OpenTelemetry\Contrib\Zipkin\Exporter;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

final class ZipkinSpanExporterFactory implements SpanExporterFactoryInterface
{
    public function createFromOptions(array $options): SpanExporterInterface
    {
        $transport = PsrTransportFactory::discover()->create($options['endpoint'], 'application/json');

        return new Exporter($transport);
    }
}
