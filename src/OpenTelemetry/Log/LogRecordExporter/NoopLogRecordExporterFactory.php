<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Log\LogRecordExporter;

use GaelReyrol\OpenTelemetryBundle\OpenTelemetry\OtlpExporterCompressionEnum;
use GaelReyrol\OpenTelemetryBundle\OpenTelemetry\OtlpExporterFormatEnum;
use OpenTelemetry\SDK\Logs\Exporter\NoopExporter;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;

final class NoopLogRecordExporterFactory implements LogRecordExporterFactoryInterface
{
    public static function create(
        string $endpoint = null,
        array $headers = [],
        OtlpExporterFormatEnum $format = null,
        OtlpExporterCompressionEnum $compression = null,
    ): LogRecordExporterInterface {
        return new NoopExporter();
    }
}
