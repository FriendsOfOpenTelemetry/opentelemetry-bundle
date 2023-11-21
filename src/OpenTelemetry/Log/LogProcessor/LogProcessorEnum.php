<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor;

enum LogProcessorEnum: string
{
    case Multi = 'multi';
    case Simple = 'simple';
    //    case Batch = 'batch';
    case Noop = 'noop';
}
