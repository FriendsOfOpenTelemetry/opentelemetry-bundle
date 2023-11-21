<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter;

use GaelReyrol\OpenTelemetryBundle\OpenTelemetry\OtlpExporterCompressionEnum;
use GaelReyrol\OpenTelemetryBundle\OpenTelemetry\OtlpExporterFormatEnum;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\MetricExporter\ConsoleMetricExporter;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;

final class ConsoleMetricExporterFactory implements MetricExporterFactoryInterface
{
    public static function create(
        string $endpoint = null,
        array $headers = [],
        OtlpExporterCompressionEnum $compression = null,
        OtlpExporterFormatEnum $format = null,
        MetricTemporalityEnum $temporality = null,
    ): MetricExporterInterface {
        return new ConsoleMetricExporter(self::getTemporality($temporality));
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
