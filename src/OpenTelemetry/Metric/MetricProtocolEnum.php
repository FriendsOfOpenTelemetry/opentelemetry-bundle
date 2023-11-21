<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Metric;

enum MetricProtocolEnum: string
{
    case Stream = 'stream';
    case Psr = 'psr';
    case Grpc = 'grpc';
}
