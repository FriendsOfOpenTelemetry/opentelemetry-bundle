<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use OpenTelemetry\SDK\Logs\Exporter\InMemoryExporter;

final class InMemoryLogExporterFactory implements LogExporterFactoryInterface
{
    public static function create(ExporterDsn $dsn, ExporterOptionsInterface $options): InMemoryExporter
    {
        return new InMemoryExporter();
    }
}
