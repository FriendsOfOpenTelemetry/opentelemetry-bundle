<?php

namespace GaelReyrol\OpenTelemetryBundle\Factory\SpanExporter;

use OpenTelemetry\SDK\Registry;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporter;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

final readonly class ConsoleSpanExporterFactory implements SpanExporterFactoryInterface
{
    public static function createFromOptions(array $options): SpanExporterInterface
    {
        $transport = Registry::transportFactory('stream')->create('php://stdout', 'application/json');

        return new ConsoleSpanExporter($transport);
    }
}
