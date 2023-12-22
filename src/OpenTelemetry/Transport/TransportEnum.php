<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport;

enum TransportEnum: string
{
    case Grpc = 'grpc';
    case Grpcs = 'grpcs';
    case Http = 'http';
    case Https = 'https';
    case Stream = 'stream';

    /**
     * @return class-string<TransportFactoryInterface>
     */
    public function getFactoryClass(): string
    {
        return match ($this) {
            self::Grpc, self::Grpcs => GrpcTransportFactory::class,
            self::Http, self::Https => OtlpHttpTransportFactory::class,
            self::Stream => StreamTransportFactory::class,
        };
    }

    public function getScheme(): ?string
    {
        return match ($this) {
            self::Http, self::Grpc => 'http',
            self::Https, self::Grpcs => 'https',
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
}
