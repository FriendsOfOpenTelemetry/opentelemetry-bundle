<?php

namespace GaelReyrol\OpenTelemetryBundle\AttributeEnum;

enum ConsoleTraceAttributeEnum: string
{
    case ExitCode = 'symfony.console.exit_code';
    case SignalCode = 'symfony.console.signal_code';
}
