<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use OpenTelemetry\SDK\Logs\Exporter\InMemoryExporter;

final class InMemoryLogExporterFactory extends AbstractLogExporterFactory
{
    public function supports(#[\SensitiveParameter] ExporterDsn $dsn, ExporterOptionsInterface $options): bool
    {
        return LogExporterEnum::InMemory === LogExporterEnum::tryFrom($dsn->getExporter());
    }

    public function createExporter(#[\SensitiveParameter] ExporterDsn $dsn, ExporterOptionsInterface $options): InMemoryExporter
    {
        return new InMemoryExporter();
    }
}
