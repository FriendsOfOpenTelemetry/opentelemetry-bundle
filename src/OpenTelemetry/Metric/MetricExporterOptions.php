<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\MetricTemporalityEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportParams;

/**
 * @phpstan-import-type ExporterOptions from ExporterOptionsInterface
 */
final class MetricExporterOptions implements ExporterOptionsInterface
{
    public function __construct(
        private MetricTemporalityEnum $temporality = MetricTemporalityEnum::Delta,
        private ?OtlpExporterOptions $otlpOptions = new OtlpExporterOptions(),
    ) {
    }

    /**
     * @param array{
     *     temporality?: string,
     * } $configuration
     */
    public static function fromConfiguration(array $configuration): self
    {
        $options = new self();

        if (isset($configuration['temporality'])) {
            $options->temporality = MetricTemporalityEnum::from($configuration['temporality']);
        }

        $options->otlpOptions = OtlpExporterOptions::fromConfiguration($configuration);

        return $options;
    }

    public function getTemporality(): MetricTemporalityEnum
    {
        return $this->temporality;
    }

    public function getOtlpOptions(): ?OtlpExporterOptions
    {
        return $this->otlpOptions;
    }

    public function toTransportParams(): TransportParams
    {
        return $this->otlpOptions->toTransportParams();
    }
}
