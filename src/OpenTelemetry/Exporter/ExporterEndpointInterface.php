<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter;

interface ExporterEndpointInterface extends \Stringable
{
    public function getTransport(): ?string;

    public static function fromDsn(ExporterDsn $dsn): self;
}
