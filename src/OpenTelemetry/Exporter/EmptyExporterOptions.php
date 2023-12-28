<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter;

class EmptyExporterOptions implements ExporterOptionsInterface
{
    public static function fromConfiguration(array $configuration): ExporterOptionsInterface
    {
        return new self();
    }
}
