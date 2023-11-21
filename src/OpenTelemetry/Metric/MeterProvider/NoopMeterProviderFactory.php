<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider;

use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Metrics\NoopMeterProvider;

final class NoopMeterProviderFactory implements MeterProviderFactoryInterface
{
    public static function create(MetricExporterInterface $exporter, ExemplarFilterInterface $filter): MeterProviderInterface
    {
        return new NoopMeterProvider();
    }
}
