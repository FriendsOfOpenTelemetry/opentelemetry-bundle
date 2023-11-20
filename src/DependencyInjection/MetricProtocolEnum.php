<?php

namespace GaelReyrol\OpenTelemetryBundle\DependencyInjection;

enum MetricProtocolEnum: string
{
    case Stream = 'stream';
    case Psr = 'psr';
    case Grpc = 'grpc';
}
