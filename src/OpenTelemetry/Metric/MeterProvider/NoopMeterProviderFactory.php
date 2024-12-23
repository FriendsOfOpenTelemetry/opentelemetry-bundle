<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider;

use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Metrics\NoopMeterProvider;
use OpenTelemetry\SDK\Resource\ResourceInfo;

final class NoopMeterProviderFactory extends AbstractMeterProviderFactory
{
    public function createProvider(?MetricExporterInterface $exporter = null, ?ExemplarFilterInterface $filter = null, ?ResourceInfo $resource = null): MeterProviderInterface
    {
        return new NoopMeterProvider();
    }
}
