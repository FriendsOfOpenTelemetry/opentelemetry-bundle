<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter;

enum TraceExporterEnum: string
{
    case InMemory = 'in_memory';
    case Console = 'console';
    case Otlp = 'otlp';
    case Zipkin = 'zipkin';
}
