<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportParams;

class EmptyExporterOptions implements ExporterOptionsInterface
{
    public static function fromConfiguration(array $configuration): ExporterOptionsInterface
    {
        return new self();
    }

    public function toTransportParams(): TransportParams
    {
        return new TransportParams();
    }
}
