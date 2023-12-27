<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter;

use OpenTelemetry\Contrib\Otlp\ContentTypes;
use OpenTelemetry\Contrib\Otlp\Protocols;

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

    public function toProtocol(): string
    {
        return match ($this) {
            self::Json => Protocols::HTTP_JSON,
            self::Ndjson => Protocols::HTTP_NDJSON,
            self::Grpc => Protocols::GRPC,
            self::Protobuf => Protocols::HTTP_PROTOBUF,
        };
    }
}
