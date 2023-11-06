<?php

namespace GaelReyrol\OpenTelemetryBundle\DependencyInjection;

enum OpenTelemetryExporterEnum: string
{
    case InMemory = 'in_memory';
    case Stream = 'stream';
    case Otlp = 'otlp';
    case Grpc = 'grpc';
    case Zipkin = 'zipkin';
}
