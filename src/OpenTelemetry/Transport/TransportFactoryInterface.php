<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterEndpointInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use OpenTelemetry\SDK\Common\Export\TransportInterface;

interface TransportFactoryInterface
{
    public static function fromExporter(ExporterEndpointInterface $endpoint, ExporterOptionsInterface $options): self;

    public static function supportExporter(ExporterEndpointInterface $endpoint, ExporterOptionsInterface $options): bool;

    /**
     * @return TransportInterface<string>
     */
    public function create(): TransportInterface;
}
