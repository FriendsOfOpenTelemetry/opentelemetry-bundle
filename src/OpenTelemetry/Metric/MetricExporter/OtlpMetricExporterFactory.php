<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporterEndpoint;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporterOptions;
use OpenTelemetry\Contrib\Otlp\MetricExporter;

final class OtlpMetricExporterFactory extends AbstractMetricExporterFactory
{
    public function supports(#[\SensitiveParameter] ExporterDsn $dsn, ExporterOptionsInterface $options): bool
    {
        if (false === $options instanceof MetricExporterOptions) {
            return false;
        }

        return MetricExporterEnum::Otlp === MetricExporterEnum::tryFrom($dsn->getExporter());
    }

    public function createExporter(#[\SensitiveParameter] ExporterDsn $dsn, ExporterOptionsInterface $options): MetricExporter
    {
        assert($options instanceof MetricExporterOptions);

        return new MetricExporter($this->transportFactory->createTransport(
            MetricExporterEndpoint::fromDsn($dsn),
            $options->getOtlpOptions(),
        ), $options->getTemporality()->toData());
    }
}
