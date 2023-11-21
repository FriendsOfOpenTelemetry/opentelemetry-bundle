<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter;

enum LogExporterEnum: string
{
    case Default = 'default';
    case Noop = 'noop';
    case Console = 'console';
    case InMemory = 'in_memory';
}
