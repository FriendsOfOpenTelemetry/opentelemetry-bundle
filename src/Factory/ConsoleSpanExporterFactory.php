<?php

namespace GaelReyrol\OpenTelemetryBundle\Factory;

use OpenTelemetry\SDK\Registry;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporter;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

final class ConsoleSpanExporterFactory implements SpanExporterFactoryInterface
{
    public function createFromOptions(array $options): SpanExporterInterface
    {
        $transport = Registry::transportFactory('stream')->create('php://stdout', 'application/json');

        return new ConsoleSpanExporter($transport);
    }
}
