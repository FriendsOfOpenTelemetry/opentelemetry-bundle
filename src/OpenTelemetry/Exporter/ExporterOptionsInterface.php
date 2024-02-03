<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportParams;

/**
 * @phpstan-type ExporterOptions array{
 *     format?: string,
 *     headers?: array<string, string>,
 *     compression?: string,
 *     timeout?: float,
 *     retry?: int,
 *     max?: int,
 *     ca?: string,
 *     cert?: string,
 *     key?: string,
 * }
 */
interface ExporterOptionsInterface
{
    /**
     * @param ExporterOptions $configuration
     */
    public static function fromConfiguration(array $configuration): self;

    public function toTransportParams(): TransportParams;
}
