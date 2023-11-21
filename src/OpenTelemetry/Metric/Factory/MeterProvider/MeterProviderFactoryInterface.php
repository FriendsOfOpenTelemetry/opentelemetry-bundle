<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Metric\Factory\MeterProvider;

use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;

interface MeterProviderFactoryInterface
{
    public static function create(MetricExporterInterface $exporter, ExemplarFilterInterface $filter): MeterProviderInterface;
}
