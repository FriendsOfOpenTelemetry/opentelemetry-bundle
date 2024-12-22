<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider;

use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;

final class DefaultMeterProviderFactory extends AbstractMeterProviderFactory
{
    public function createProvider(MetricExporterInterface $exporter, ExemplarFilterInterface $filter, ?ResourceInfo $resource = null): MeterProviderInterface
    {
        $reader = new ExportingReader($exporter);

        return MeterProvider::builder()
            ->setResource($resource ?? ResourceInfoFactory::defaultResource())
            ->addReader($reader)
            ->setExemplarFilter($filter)
            ->build();
    }
}
