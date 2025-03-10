<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\ExemplarFilterEnum;
use OpenTelemetry\API\Metrics\MeterInterface;
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
            $this->container->setAlias(MeterInterface::class, new Reference(sprintf('open_telemetry.metrics.meters.%s', $defaultMeter)));
        }
    }

    /**
     * @param array{
     *     dsn: string,
     *     temporality: string,
     *     options?: ExporterOptions
     * } $config
     */
    private function loadMetricExporter(string $name, array $config): void
    {
        $dsn = (new ChildDefinition('open_telemetry.exporter_dsn'))->setArguments([$config['dsn']]);
        $exporterOptions = (new ChildDefinition('open_telemetry.metric_exporter_options'))->setArguments([
            [
                $config['temporality'],
                ...$config['options'] ?? [],
            ],
        ]);

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
     *     filter?: array{type: string, service_id?: string, options?: array<int, mixed>}
     * } $config
     */
    private function loadMetricProvider(string $name, array $config): void
    {
        $filter = (new ChildDefinition('open_telemetry.metrics.exemplar_filter_factory'));

        $params = [];
        if (isset($config['filter']['type']) && ExemplarFilterEnum::Service->value === $config['filter']['type']) {
            if (!array_key_exists('service_id', $config['filter'])) {
                throw new \LogicException('To configure an exemplar filter of type service, you must specify the service_id key');
            }
            $params['service_id'] = new Reference($config['filter']['service_id']);
        }
        $filter->setArguments([
            $config['filter']['type'] ?? ExemplarFilterEnum::All->value,
            $params,
        ]);

        $this->container
            ->setDefinition(
                sprintf('open_telemetry.metrics.providers.%s', $name),
                new ChildDefinition('open_telemetry.metrics.provider_interface'),
            )
            ->setFactory([new Reference(sprintf('open_telemetry.metrics.provider_factory.%s', $config['type'])), 'createProvider'])
            ->setArguments([
                isset($config['exporter']) ? new Reference($config['exporter']) : null,
                $filter,
                new Reference('open_telemetry.resource_info'),
            ])
            ->addTag('open_telemetry.metrics.provider')
        ;
    }

    /**
     * @param array{
     *     provider: string,
     *     name?: string,
     *     version?: string,
     * } $config
     */
    private function loadMetricMeter(string $name, array $config): void
    {
        $this->container
            ->setDefinition(
                sprintf('open_telemetry.metrics.meters.%s', $name),
                new ChildDefinition('open_telemetry.metrics.meter_interface')
            )
            ->setPublic(true)
            ->setFactory([new Reference($config['provider']), 'getMeter'])
            ->setArguments([
                $config['name'] ?? $this->container->getParameter('open_telemetry.bundle.name'),
                $config['version'] ?? $this->container->getParameter('open_telemetry.bundle.version'),
            ]);
    }
}
