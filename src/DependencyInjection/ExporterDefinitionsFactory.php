<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final readonly class ExporterDefinitionsFactory
{
    public const EXPORTER_OPTIONS = ['format', 'headers', 'compression', 'timeout', 'retry', 'max', 'ca', 'cert', 'key'];
    public const METRIC_EXPORTER_OPTIONS = ['temporality'];

    public function __construct(private ContainerBuilder $container)
    {
    }

    public function createExporterDsnDefinition(string $dsn): Definition
    {
        return $this->container
            ->getDefinition('open_telemetry.exporter_dsn')
            ->setArguments([$dsn]);
    }

    /**
     * @param array<string, mixed> $configuration
     * @param string[]             $extraOptions
     */
    public function createExporterOptionsDefinition(
        array $configuration,
        string $definition = 'open_telemetry.exporter_options',
        array $extraOptions = [],
    ): Definition {
        return $this->container
            ->getDefinition($definition)
            ->setArguments([array_filter(
                $configuration,
                fn (string $key) => in_array(
                    $key,
                    self::EXPORTER_OPTIONS + $extraOptions,
                    true,
                ), ARRAY_FILTER_USE_KEY),
            ]);
    }
}
