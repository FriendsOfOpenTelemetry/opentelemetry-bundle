<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\ExtensionLoader;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\ExporterDefinitionsFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider\ExemplarFilterEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider\MeterProviderEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider\MeterProviderFactoryInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\MetricExporterEnum;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @phpstan-import-type ExporterOptions from ExporterOptionsInterface
 */
final class MetricsExtensionLoader implements ExtensionLoaderInterface
{
    private array $config;

    private ContainerBuilder $container;

    /**
     * @param array{
     *     default_meter?: string,
     *     meters: array<string, mixed>,
     *     exporters: array<string, mixed>,
     *     providers: array<string, mixed>
     * } $config
     */
    public function load(array $config, ContainerBuilder $container): void
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

        $defaultMeter = $this->config['default_meter'] ?? null;
        if (0 < count($this->config['meters'])) {
            $defaultMeter = array_key_first($this->config['meters']);
        }

        if (null !== $defaultMeter) {
            $this->container->set('open_telemetry.metrics.default_meter', new Reference(sprintf('open_telemetry.metrics.meters.%s', $defaultMeter)));
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
        $exporterId = sprintf('open_telemetry.metrics.exporters.%s', $name);
        $dsn = ExporterDsn::fromString($options['dsn']);
        $exporter = MetricExporterEnum::from($dsn->getExporter());

        $exporterDefinitionsFactory = new ExporterDefinitionsFactory($this->container);

        $this->container
            ->setDefinition($exporterId, new ChildDefinition('open_telemetry.metrics.exporter'))
            ->setClass($exporter->getClass())
            ->setFactory([$exporter->getFactoryClass(), 'createExporter'])
            ->setArguments([
                '$dsn' => $exporterDefinitionsFactory->createExporterDsnDefinition($options['dsn']),
                '$options' => $exporterDefinitionsFactory->createExporterOptionsDefinition(
                    $options['options'] ?? [],
                    'open_telemetry.metric_exporter_options',
                    ExporterDefinitionsFactory::METRIC_EXPORTER_OPTIONS,
                ),
            ]);
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
        $providerId = sprintf('open_telemetry.metrics.providers.%s', $name);
        $options = $this->getMetricProviderOptions($provider);

        $this->container
            ->setDefinition($providerId, new ChildDefinition('open_telemetry.metrics.provider'))
            ->setClass($options['class'])
            ->setFactory([$options['factory'], 'createProvider'])
            ->setArguments([
                '$exporter' => $options['exporter'],
                '$filter' => $options['filter'],
            ]);
    }

    /**
     * @param array{
     *     type: string,
     *     exporter?: string,
     *     filter?: string,
     * } $provider
     *
     * @return array{
     *     factory: class-string<MeterProviderFactoryInterface>,
     *     class: class-string<MeterProviderInterface>,
     *     exporter: ?Reference,
     *     filter: ?Reference,
     * }
     */
    private function getMetricProviderOptions(array $provider): array
    {
        $providerEnum = MeterProviderEnum::from($provider['type']);
        $options = [
            'factory' => $providerEnum->getFactoryClass(),
            'class' => $providerEnum->getClass(),
            'exporter' => null,
            'filter' => null,
        ];

        if (MeterProviderEnum::Default === $providerEnum) {
            if (!isset($provider['exporter'])) {
                throw new \InvalidArgumentException('Exporter is not set');
            }
            $options['exporter'] = new Reference(sprintf('open_telemetry.metrics.exporters.%s', $provider['exporter']));
        }

        $filter = isset($provider['filter']) ? ExemplarFilterEnum::from($provider['filter']) : ExemplarFilterEnum::All;
        $options['filter'] = match ($filter) {
            ExemplarFilterEnum::WithSampledTrace => new Reference('open_telemetry.metrics.exemplar_filters.with_sampled_trace'),
            ExemplarFilterEnum::All => new Reference('open_telemetry.metrics.exemplar_filters.all'),
            ExemplarFilterEnum::None => new Reference('open_telemetry.metrics.exemplar_filters.none'),
        };

        return $options;
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
        $meterId = sprintf('open_telemetry.metrics.meters.%s', $name);

        $this->container
            ->setDefinition($meterId, new ChildDefinition('open_telemetry.metrics.meter'))
            ->setPublic(true)
            ->setFactory([
                new Reference(sprintf('open_telemetry.metrics.providers.%s', $meter['provider'])),
                'getMeter',
            ])
            ->setArguments([
                $meter['name'] ?? $this->container->getParameter('open_telemetry.bundle.name'),
                $meter['version'] ?? $this->container->getParameter('open_telemetry.bundle.version'),
            ]);
    }
}
