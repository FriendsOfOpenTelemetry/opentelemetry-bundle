<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor;

enum LogProcessorEnum: string
{
    case Batch = 'batch';
    case Multi = 'multi';
    case Noop = 'noop';
    case Simple = 'simple';
}
