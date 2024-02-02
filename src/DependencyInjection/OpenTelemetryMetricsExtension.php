<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\ExemplarFilterEnum;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @phpstan-import-type ExporterOptions from ExporterOptionsInterface
 */
final class OpenTelemetryMetricsExtension
{
    /**
     * @var array<string, mixed>
     */
    private array $config;

    private ContainerBuilder $container;

    /**
     * @param array{
     *     default_meter?: string,
     *     meters: array<string, mixed>,
     *     exporters: array<string, mixed>,
     *     providers: array<string, mixed>
     * }|array<string, mixed> $config
     */
    public function __invoke(array $config, ContainerBuilder $container): void
    {
        $this->config = $config;
        $this->container = $container;

        foreach ($this->config['exporters'] as $name => $exporter) {
            $this->loadMetricExporter($name, $exporter);
        }

        foreach ($this->config['providers'] as $name => $provider) {
            $this->loadMetricProvider($name, $provider);
        }

        foreach ($this->config['meters'] as $name => $meter) {
            $this->loadMetricMeter($name, $meter);
        }

        $defaultMeter = null;
        if (0 < count($this->config['meters'])) {
            $defaultMeter = array_key_first($this->config['meters']);
        }

        if (null !== $defaultMeter) {
            $this->container->setAlias('open_telemetry.metrics.default_meter', new Reference(sprintf('open_telemetry.metrics.meters.%s', $defaultMeter)));
        }
    }

    /**
     * @param array{
     *     dsn: string,
     *     options?: ExporterOptions
     * } $options
     */
    private function loadMetricExporter(string $name, array $options): void
    {
        $dsn = $this->container->getDefinition('open_telemetry.exporter_dsn')->setArguments([$options['dsn']]);
        $exporterOptions = $this->container->getDefinition('open_telemetry.metric_exporter_options')->setArguments([$options['options'] ?? []]);

        $this->container
            ->setDefinition(
                sprintf('open_telemetry.metrics.exporters.%s', $name),
                new ChildDefinition('open_telemetry.metrics.exporter_interface'),
            )
            ->setArguments([$dsn, $exporterOptions]);
    }

    /**
     * @param array{
     *     type: string,
     *     exporter?: string,
     *     filter?: string
     * } $provider
     */
    private function loadMetricProvider(string $name, array $provider): void
    {
        $filter = $this->container->getDefinition('open_telemetry.metrics.exemplar_factory')->setArguments([$provider['filter'] ?? ExemplarFilterEnum::All->value]);

        $this->container
            ->setDefinition(
                sprintf('open_telemetry.metrics.providers.%s', $name),
                new ChildDefinition('open_telemetry.metrics.provider_interface'),
            )
            ->setFactory([new Reference(sprintf('open_telemetry.metrics.provider_factory.%s', $provider['type'])), 'createProvider'])
            ->setArguments([
                new Reference($provider['exporter'] ?? '', ContainerBuilder::NULL_ON_INVALID_REFERENCE),
                $filter,
            ]);
    }

    /**
     * @param array{
     *     provider: string,
     *     name?: string,
     *     version?: string,
     * } $meter
     */
    private function loadMetricMeter(string $name, array $meter): void
    {
        $this->container
            ->setDefinition(
                sprintf('open_telemetry.metrics.meters.%s', $name),
                new ChildDefinition('open_telemetry.metrics.meter')
            )
            ->setPublic(true)
            ->setFactory([new Reference($meter['provider']), 'getMeter'])
            ->setArguments([
                $meter['name'] ?? $this->container->getParameter('open_telemetry.bundle.name'),
                $meter['version'] ?? $this->container->getParameter('open_telemetry.bundle.version'),
            ]);
    }
}
