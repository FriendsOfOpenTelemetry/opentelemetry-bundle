<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Trace\Factory\SpanExporter;

use GaelReyrol\OpenTelemetryBundle\OpenTelemetry\OtlpExporterCompressionEnum;
use GaelReyrol\OpenTelemetryBundle\OpenTelemetry\OtlpExporterFormatEnum;
use OpenTelemetry\SDK\Trace\SpanExporter\InMemoryExporter;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

final readonly class InMemorySpanExporterFactory implements SpanExporterFactoryInterface
{
    public static function create(
        string $endpoint,
        array $headers,
        OtlpExporterFormatEnum $format = null,
        OtlpExporterCompressionEnum $compression = null,
    ): SpanExporterInterface {
        return new InMemoryExporter();
    }
}
