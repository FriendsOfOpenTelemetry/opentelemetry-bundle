<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter;

use OpenTelemetry\Contrib\Otlp\SpanExporter as OtlpSpanExporter;
use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinSpanExporter;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporter;
use OpenTelemetry\SDK\Trace\SpanExporter\InMemoryExporter;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

enum TraceExporterEnum: string
{
    case InMemory = 'in_memory';
    case Console = 'console';
    case Otlp = 'otlp';
    case Zipkin = 'zipkin';

    /**
     * @return class-string<SpanExporterFactoryInterface>
     */
    public function getFactoryClass(): string
    {
        return match ($this) {
            self::Console => ConsoleSpanExporterFactory::class,
            self::InMemory => InMemorySpanExporterFactory::class,
            self::Otlp => OtlpSpanExporterFactory::class,
            self::Zipkin => ZipkinSpanExporterFactory::class,
        };
    }

    /**
     * @return class-string<SpanExporterInterface>
     */
    public function getClass(): string
    {
        return match ($this) {
            self::Console => ConsoleSpanExporter::class,
            self::InMemory => InMemoryExporter::class,
            self::Otlp => OtlpSpanExporter::class,
            self::Zipkin => ZipkinSpanExporter::class,
        };
    }
}
