<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor;

enum LogProcessorEnum: string
{
    case Multi = 'multi';
    case Noop = 'noop';
    case Simple = 'simple';
}
