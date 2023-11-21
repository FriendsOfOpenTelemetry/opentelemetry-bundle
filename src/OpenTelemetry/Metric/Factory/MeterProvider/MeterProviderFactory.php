<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Metric\Factory\MeterProvider;

use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;

final class MeterProviderFactory implements MeterProviderFactoryInterface
{
    public static function create(MetricExporterInterface $exporter, ExemplarFilterInterface $filter): MeterProviderInterface
    {
        $reader = new ExportingReader($exporter);
        $resource = ResourceInfoFactory::defaultResource();

        return MeterProvider::builder()
            ->setResource($resource)
            ->addReader($reader)
            ->setExemplarFilter($filter)
            ->build();
    }
}
