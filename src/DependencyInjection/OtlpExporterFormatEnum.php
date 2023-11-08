<?php

namespace GaelReyrol\OpenTelemetryBundle\DependencyInjection;

enum OtlpExporterFormatEnum: string
{
    case Json = 'json';
    case Ndjson = 'ndjson';
    case Grpc = 'gprc';
    case Protobuf = 'protobuf';
}
