<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\OtlpExporterCompressionEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\OtlpExporterFormatEnum;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;

interface LogExporterFactoryInterface
{
    /**
     * @param array<string, string> $headers
     */
    public static function create(
        string $endpoint = null,
        array $headers = [],
        OtlpExporterFormatEnum $format = null,
        OtlpExporterCompressionEnum $compression = null,
    ): LogRecordExporterInterface;
}
