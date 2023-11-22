<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter;

enum LogExporterEnum: string
{
    case Otlp = 'otlp';
    case Noop = 'noop';
    case Console = 'console';
    case InMemory = 'in_memory';
}
