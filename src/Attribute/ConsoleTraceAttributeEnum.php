<?php

namespace GaelReyrol\OpenTelemetryBundle\Attribute;

enum ConsoleTraceAttributeEnum: string
{
    case ExitCode = 'symfony.console.exit_code';
    case Signal = 'symfony.console.signal';
}
