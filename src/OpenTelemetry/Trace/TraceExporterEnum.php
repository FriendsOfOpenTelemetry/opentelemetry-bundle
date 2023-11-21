<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Trace;

enum TraceExporterEnum: string
{
    case InMemory = 'in_memory';
    case Console = 'console';
    case Otlp = 'otlp';
    case Zipkin = 'zipkin';
}
