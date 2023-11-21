<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Context\Attribute;

enum ConsoleTraceAttributeEnum: string
{
    case ExitCode = 'symfony.console.exit_code';
    case SignalCode = 'symfony.console.signal_code';
}
