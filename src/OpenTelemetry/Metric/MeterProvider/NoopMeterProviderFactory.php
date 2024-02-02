<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider;

use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Metrics\NoopMeterProvider;

final class NoopMeterProviderFactory extends AbstractMeterProviderFactory
{
    public static function createProvider(?MetricExporterInterface $exporter = null, ?ExemplarFilterInterface $filter = null): MeterProviderInterface
    {
        return new NoopMeterProvider();
    }
}
