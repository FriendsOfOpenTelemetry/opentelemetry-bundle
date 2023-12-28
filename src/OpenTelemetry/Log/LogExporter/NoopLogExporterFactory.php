<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use OpenTelemetry\SDK\Logs\Exporter\NoopExporter;

final class NoopLogExporterFactory implements LogExporterFactoryInterface
{
    public static function createExporter(ExporterDsn $dsn = null, ExporterOptionsInterface $options = null): NoopExporter
    {
        return new NoopExporter();
    }
}
