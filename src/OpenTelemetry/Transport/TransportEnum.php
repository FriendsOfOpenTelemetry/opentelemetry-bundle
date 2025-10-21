<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;

enum TransportEnum: string
{
    case Grpc = 'grpc';
    case Grpcs = 'grpcs';
    case Http = 'http';
    case Https = 'https';
    case Stream = 'stream';
    case Kafka = 'kafka';

    public function getScheme(): ?string
    {
        return match ($this) {
            self::Http, self::Grpc => 'http',
            self::Https, self::Grpcs => 'https',
            self::Kafka => 'kafka',
            default => null,
        };
    }

    public function getPort(): ?int
    {
        return match ($this) {
            self::Grpc, self::Grpcs => 4317,
            self::Http, self::Https => 4318,
            default => null,
        };
    }

    public static function fromDsn(ExporterDsn $dsn): ?self
    {
        return TransportEnum::tryFrom($dsn->getTransport() ?? '');
    }
}
