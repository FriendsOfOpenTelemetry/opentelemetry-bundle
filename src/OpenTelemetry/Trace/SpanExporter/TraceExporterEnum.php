<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;

enum TraceExporterEnum: string
{
    case InMemory = 'in-memory';
    case Console = 'console';
    case Otlp = 'otlp';
    case Zipkin = 'zipkin';

    public static function fromDsn(ExporterDsn $dsn): self
    {
        $exporter = self::tryFrom($dsn->getExporter());

        if (null === $exporter) {
            throw new \InvalidArgumentException('Unsupported DSN for Trace exporter');
        }

        return $exporter;
    }
}
