<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider;

use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;

interface MeterProviderFactoryInterface
{
    public function createProvider(MetricExporterInterface $exporter, ExemplarFilterInterface $filter, ?ResourceInfo $resource = null): MeterProviderInterface;
}
