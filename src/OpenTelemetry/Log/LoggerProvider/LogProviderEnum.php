<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider;

enum LogProviderEnum: string
{
    case Default = 'default';
    case Noop = 'noop';
}
