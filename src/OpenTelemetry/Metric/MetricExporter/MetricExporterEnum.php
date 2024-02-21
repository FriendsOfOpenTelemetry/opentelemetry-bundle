<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;

enum MetricExporterEnum: string
{
    case Noop = 'noop';
    case Otlp = 'otlp';

    case Console = 'console';
    case InMemory = 'in-memory';

    public static function fromDsn(ExporterDsn $dsn): self
    {
        $exporter = self::tryFrom($dsn->getExporter());

        if (null === $exporter) {
            throw new \InvalidArgumentException('Unsupported DSN for Metric exporter');
        }

        return self::from($dsn->getExporter());
    }
}
