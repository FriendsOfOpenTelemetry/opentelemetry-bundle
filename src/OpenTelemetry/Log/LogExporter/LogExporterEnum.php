<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use OpenTelemetry\Contrib\Otlp\LogsExporter;
use OpenTelemetry\SDK\Logs\Exporter\ConsoleExporter;
use OpenTelemetry\SDK\Logs\Exporter\InMemoryExporter;
use OpenTelemetry\SDK\Logs\Exporter\NoopExporter;

enum LogExporterEnum: string
{
    case Console = 'console';
    case InMemory = 'in-memory';
    case Noop = 'noop';
    case Otlp = 'otlp';

    public function getFactoryClass(): string
    {
        return match ($this) {
            self::Console => ConsoleLogExporterFactory::class,
            self::InMemory => InMemoryLogExporterFactory::class,
            self::Otlp => OtlpLogExporterFactory::class,
            self::Noop => NoopLogExporterFactory::class,
        };
    }

    public function getClass(): string
    {
        return match ($this) {
            self::Console => ConsoleExporter::class,
            self::InMemory => InMemoryExporter::class,
            self::Otlp => LogsExporter::class,
            self::Noop => NoopExporter::class,
        };
    }

    public static function fromDsn(ExporterDsn $dsn): self
    {
        $exporter = self::tryFrom($dsn->getExporter());

        if (null === $exporter) {
            throw new \InvalidArgumentException('Unsupported DSN exporter.');
        }

        return $exporter;
    }
}
