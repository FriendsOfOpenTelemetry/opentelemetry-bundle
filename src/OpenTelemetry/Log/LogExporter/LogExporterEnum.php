<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;

enum LogExporterEnum: string
{
    case Console = 'console';
    case InMemory = 'in-memory';
    case Noop = 'noop';
    case Otlp = 'otlp';

    public static function fromDsn(ExporterDsn $dsn): self
    {
        $exporter = self::tryFrom($dsn->getExporter());

        if (null === $exporter) {
            throw new \InvalidArgumentException('Unsupported DSN exporter.');
        }

        return $exporter;
    }
}
