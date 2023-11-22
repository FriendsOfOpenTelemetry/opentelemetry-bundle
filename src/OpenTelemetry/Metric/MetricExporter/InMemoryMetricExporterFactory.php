<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter;

use GaelReyrol\OpenTelemetryBundle\OpenTelemetry\OtlpExporterCompressionEnum;
use GaelReyrol\OpenTelemetryBundle\OpenTelemetry\OtlpExporterFormatEnum;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\MetricExporter\InMemoryExporter;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;

final class InMemoryMetricExporterFactory implements MetricExporterFactoryInterface
{
    public static function create(
        string $endpoint = null,
        array $headers = null,
        OtlpExporterCompressionEnum $compression = null,
        OtlpExporterFormatEnum $format = null,
        MetricTemporalityEnum $temporality = null,
    ): MetricExporterInterface {
        return new InMemoryExporter(self::getTemporality($temporality));
    }

    private static function getTemporality(MetricTemporalityEnum $temporality = null): string
    {
        return match ($temporality) {
            MetricTemporalityEnum::Delta => Temporality::DELTA,
            MetricTemporalityEnum::Cumulative => Temporality::CUMULATIVE,
            MetricTemporalityEnum::LowMemory, null => null,
        };
    }
}
