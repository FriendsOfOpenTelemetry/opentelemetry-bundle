<?php

namespace GaelReyrol\OpenTelemetryBundle\DependencyInjection;

enum MetricExporterEnum: string
{
    case Noop = 'noop';
    case Default = 'default';
    case Console = 'console';
    case InMemory = 'in_memory';
    case PushDefault = 'push_default';
    case PushConsole = 'push_console';
}
