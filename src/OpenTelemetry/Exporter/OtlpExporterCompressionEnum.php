<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter;

use OpenTelemetry\SDK\Common\Configuration\KnownValues;

enum OtlpExporterCompressionEnum: string
{
    case Gzip = 'gzip';
    case None = 'none';

    public function toKnownValue(): string
    {
        return match ($this) {
            self::Gzip => KnownValues::VALUE_GZIP,
            self::None => KnownValues::VALUE_NONE,
        };
    }
}
