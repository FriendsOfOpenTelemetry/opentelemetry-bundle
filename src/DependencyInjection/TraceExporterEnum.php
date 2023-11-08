<?php

namespace GaelReyrol\OpenTelemetryBundle\DependencyInjection;

enum TraceExporterEnum: string
{
    case InMemory = 'in_memory';
    case Console = 'console';
    case Otlp = 'otlp';
    case Zipkin = 'zipkin';
}
