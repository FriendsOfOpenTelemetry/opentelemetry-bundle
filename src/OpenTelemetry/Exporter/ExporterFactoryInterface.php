<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter;

/**
 * @template T
 */
interface ExporterFactoryInterface
{
    /**
     * @return T
     */
    public static function create(ExporterDsn $dsn, ExporterOptionsInterface $options);
}
