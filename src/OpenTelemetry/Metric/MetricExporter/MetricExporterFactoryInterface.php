<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\OtlpExporterCompressionEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\OtlpExporterFormatEnum;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;

interface MetricExporterFactoryInterface
{
    /**
     * @param array<string, string> $headers
     */
    public static function create(
        string $endpoint = null,
        array $headers = null,
        OtlpExporterCompressionEnum $compression = null,
        OtlpExporterFormatEnum $format = null,
        MetricTemporalityEnum $temporality = null,
    ): MetricExporterInterface;
}
