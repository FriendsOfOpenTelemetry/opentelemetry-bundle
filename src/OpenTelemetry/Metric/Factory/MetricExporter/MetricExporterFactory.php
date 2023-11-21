<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Metric\Factory\MetricExporter;

use GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Metric\MetricProtocolEnum;
use GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Metric\MetricTemporalityEnum;
use GaelReyrol\OpenTelemetryBundle\OpenTelemetry\OtlpExporterCompressionEnum;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;

final class MetricExporterFactory implements MetricExporterFactoryInterface
{
    public static function create(
        string $endpoint = null,
        array $headers = null,
        OtlpExporterCompressionEnum $compression = null,
        MetricTemporalityEnum $temporality = null,
        MetricProtocolEnum $protocol = null,
    ): MetricExporterInterface {
        // TODO: Implement create() method.
    }
}
