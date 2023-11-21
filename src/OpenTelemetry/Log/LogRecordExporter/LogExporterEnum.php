<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Log\LogRecordExporter;

enum LogExporterEnum: string
{
    case Logs = 'logs';
    case Noop = 'noop';
    case Console = 'console';
    case InMemory = 'in_memory';
}
