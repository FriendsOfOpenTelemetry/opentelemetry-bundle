<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

final class SpanExporterFactory implements SpanExporterFactoryInterface
{
    /**
     * @param iterable<mixed, SpanExporterFactoryInterface> $factories
     */
    public function __construct(private readonly iterable $factories)
    {
    }

    public function supports(#[\SensitiveParameter] ExporterDsn $dsn, ExporterOptionsInterface $options): bool
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($dsn, $options)) {
                return true;
            }
        }

        return false;
    }

    public function createExporter(#[\SensitiveParameter] ExporterDsn $dsn, ExporterOptionsInterface $options): SpanExporterInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($dsn, $options)) {
                return $factory->createExporter($dsn, $options);
            }
        }

        throw new \InvalidArgumentException('No span exporter supports the given DSN.');
    }
}
