<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry;

enum MetricProtocolEnum: string
{
    case Stream = 'stream';
    case Psr = 'psr';
    case Grpc = 'grpc';
}
