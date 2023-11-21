<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry;

enum TraceExporterEnum: string
{
    case InMemory = 'in_memory';
    case Console = 'console';
    case Otlp = 'otlp';
    case Zipkin = 'zipkin';
}
