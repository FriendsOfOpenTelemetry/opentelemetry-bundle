<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter;

use OpenTelemetry\Contrib\Otlp\MetricExporter;
use OpenTelemetry\SDK\Metrics\MetricExporter\ConsoleMetricExporter;
use OpenTelemetry\SDK\Metrics\MetricExporter\InMemoryExporter;
use OpenTelemetry\SDK\Metrics\MetricExporter\NoopMetricExporter;

enum MetricExporterEnum: string
{
    case Noop = 'noop';
    case Otlp = 'otlp';

    case Console = 'console';
    case InMemory = 'in_memory';

    public function getFactoryClass(): string
    {
        return match ($this) {
            self::Console => ConsoleMetricExporterFactory::class,
            self::InMemory => InMemoryMetricExporterFactory::class,
            self::Noop => NoopMetricExporterFactory::class,
            self::Otlp => OtlpMetricExporterFactory::class,
        };
    }

    public function getClass(): string
    {
        return match ($this) {
            self::Console => ConsoleMetricExporter::class,
            self::InMemory => InMemoryExporter::class,
            self::Otlp => MetricExporter::class,
            self::Noop => NoopMetricExporter::class,
        };
    }
}
