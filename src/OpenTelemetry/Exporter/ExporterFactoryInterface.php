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
    public static function createExporter(ExporterDsn $dsn, ExporterOptionsInterface $options);
}
