<?php

namespace GaelReyrol\OpenTelemetryBundle;

enum SymfonyTraceAttributes: string
{
    case ConsoleExitCode = 'symfony.console.exit_code';
    case ConsoleSignal = 'symfony.console.signal';
}
