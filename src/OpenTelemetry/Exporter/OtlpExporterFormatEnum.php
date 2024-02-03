<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter;

use OpenTelemetry\Contrib\Otlp\ContentTypes;

enum OtlpExporterFormatEnum: string
{
    case Json = 'json';
    case Ndjson = 'ndjson';
    case Grpc = 'gprc';
    case Protobuf = 'protobuf';

    public function toContentType(): string
    {
        return match ($this) {
            self::Json => ContentTypes::JSON,
            self::Ndjson => ContentTypes::NDJSON,
            self::Grpc, self::Protobuf => ContentTypes::PROTOBUF,
        };
    }
}
