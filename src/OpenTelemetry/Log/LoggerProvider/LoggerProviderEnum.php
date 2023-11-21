<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider;

enum LoggerProviderEnum: string
{
    case Default = 'default';
    case Noop = 'noop';
}
