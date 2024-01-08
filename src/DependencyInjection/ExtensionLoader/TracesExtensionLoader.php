<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\ExtensionLoader;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\ExporterDefinitionsFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\TraceExporterEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor\SpanProcessorEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor\SpanProcessorFactoryInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TracerProvider\TraceProviderEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TracerProvider\TracerProviderFactoryInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TracerProvider\TraceSamplerEnum;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use OpenTelemetry\SDK\Trace\TracerProviderInterface;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @phpstan-import-type ExporterOptions from ExporterOptionsInterface
 */
final class TracesExtensionLoader implements ExtensionLoaderInterface
{
    private array $config;
    private ContainerBuilder $container;

    /**
     * @param array{
     *     default_tracer?: string,
     *     tracers: array<string, mixed>,
     *     exporters: array<string, mixed>,
     *     processors: array<string, mixed>,
     *     providers: array<string, mixed>
     * } $config
     */
    public function load(array $config, ContainerBuilder $container): void
    {
        $this->config = $config;
        $this->container = $container;

        foreach ($this->config['exporters'] as $name => $exporter) {
            $this->loadTraceExporter($name, $exporter);
        }

        foreach ($this->config['processors'] as $name => $processor) {
            $this->loadTraceProcessor($name, $processor);
        }

        foreach ($this->config['providers'] as $name => $provider) {
            $this->loadTraceProvider($name, $provider);
        }

        foreach ($this->config['tracers'] as $name => $tracer) {
            $this->loadTraceTracer($name, $tracer);
        }

        $defaultTracer = $this->config['default_tracer'] ?? null;
        if (0 < count($this->config['tracers'])) {
            $defaultTracer = array_key_first($this->config['tracers']);
        }

        if (null !== $defaultTracer) {
            $this->container->set('open_telemetry.traces.default_tracer', new Reference(sprintf('open_telemetry.traces.tracers.%s', $defaultTracer)));
        }
    }

    /**
     * @param array{
     *      dsn: string,
     *      options?: ExporterOptions
     *  } $options
     */
    private function loadTraceExporter(string $name, array $options): void
    {
        $exporterId = sprintf('open_telemetry.traces.exporters.%s', $name);
        $dsn = ExporterDsn::fromString($options['dsn']);
        $exporter = TraceExporterEnum::from($dsn->getExporter());

        $exporterDefinitionsFactory = new ExporterDefinitionsFactory($this->container);

        $this->container
            ->setDefinition($exporterId, new ChildDefinition('open_telemetry.traces.exporter'))
            ->setClass($exporter->getClass())
            ->setFactory([$exporter->getFactoryClass(), 'createExporter'])
            ->setArguments([
                '$dsn' => $exporterDefinitionsFactory->createExporterDsnDefinition($options['dsn']),
                '$options' => $exporterDefinitionsFactory->createExporterOptionsDefinition($options['options'] ?? []),
            ]);
    }

    /**
     * @param array{
     *      type: string,
     *      processors?: string[],
     *      exporter?: string
     *  } $processor
     */
    private function loadTraceProcessor(string $name, array $processor): void
    {
        $processorId = sprintf('open_telemetry.traces.processors.%s', $name);
        $options = $this->getTraceProcessorOptions($processor);

        $this->container
            ->setDefinition($processorId, new ChildDefinition('open_telemetry.traces.processor'))
            ->setClass($options['class'])
            ->setFactory([$options['factory'], 'createProcessor'])
            ->setArguments([
                '$processors' => $options['processors'],
                '$exporter' => $options['exporter'],
            ]);
    }

    /**
     * @param array{
     *     type: string,
     *     processors?: string[],
     *     exporter?: string
     * } $processor
     *
     * @return array{
     *     factory: class-string<SpanProcessorFactoryInterface>,
     *     class: class-string<SpanProcessorInterface>,
     *     processors: ?Reference[],
     *     exporter: ?Reference,
     * }
     */
    private function getTraceProcessorOptions(array $processor): array
    {
        $processorEnum = SpanProcessorEnum::from($processor['type']);
        $options = [
            'factory' => $processorEnum->getFactoryClass(),
            'class' => $processorEnum->getClass(),
            'processors' => [],
            'exporter' => null,
        ];

        // if (SpanProcessorEnum::Batch === $options['type']) {
        //     // TODO: Check batch options
        //     clock: OpenTelemetry\SDK\Common\Time\SystemClock
        //     max_queue_size: 2048
        //     schedule_delay: 5000
        //     export_timeout: 30000
        //     max_export_batch_size: 512
        //     auto_flush: true
        // }

        if (SpanProcessorEnum::Multi === $processorEnum) {
            if (!isset($processor['processors']) || 0 === count($processor['processors'])) {
                throw new \InvalidArgumentException('Processors are not set or empty');
            }
            $options['processors'] = array_map(
                fn (string $processor) => new Reference(sprintf('open_telemetry.traces.processors.%s', $processor)),
                $processor['processors'],
            );
        }

        if (SpanProcessorEnum::Simple === $processorEnum) {
            if (!isset($processor['exporter'])) {
                throw new \InvalidArgumentException('Exporter is not set');
            }
            $options['exporter'] = new Reference(sprintf('open_telemetry.traces.exporters.%s', $processor['exporter']));
        }

        return $options;
    }

    /**
     * @param array{
     *     type: string,
     *     sampler?: array{type: string, ratio?: float, parent?: string},
     *     processors?: string[]
     * } $provider
     */
    private function loadTraceProvider(string $name, array $provider): void
    {
        $providerId = sprintf('open_telemetry.traces.providers.%s', $name);
        $options = $this->getTraceProviderOptions($provider);

        $sampler = isset($provider['sampler']) ? $this->getTraceSamplerDefinition($provider['sampler']) : $this->container->getDefinition('open_telemetry.traces.samplers.always_on');

        $this->container
            ->setDefinition($providerId, new ChildDefinition('open_telemetry.traces.provider'))
            ->setClass($options['class'])
            ->setFactory([$options['factory'], 'createProvider'])
            ->setArguments([
                '$sampler' => $sampler,
                '$processors' => $options['processors'],
            ]);
    }

    /**
     * @param array{type: string, ratio?: float, parent?: string} $sampler
     */
    private function getTraceSamplerDefinition(array $sampler): Definition
    {
        $type = TraceSamplerEnum::from($sampler['type']);

        if (TraceSamplerEnum::TraceIdRatio === $type && !isset($sampler['ratio'])) {
            throw new \InvalidArgumentException(sprintf("Sampler of type '%s' requires a ratio parameter.", $type->value));
        }

        if (TraceSamplerEnum::ParentBased === $type) {
            if (!isset($sampler['parent'])) {
                throw new \InvalidArgumentException(sprintf("Sampler of type '%s' requires a parent parameter.", $type->value));
            }
            $parentSampler = TraceSamplerEnum::tryFrom($sampler['parent']);
            if (!in_array($parentSampler, [TraceSamplerEnum::AlwaysOn, TraceSamplerEnum::AlwaysOff], true)) {
                throw new \InvalidArgumentException(sprintf("Unsupported '%s' parent sampler", $parentSampler->value));
            }
        }

        return match ($type) {
            TraceSamplerEnum::AlwaysOn => $this->container->getDefinition('open_telemetry.traces.samplers.always_on'),
            TraceSamplerEnum::AlwaysOff => $this->container->getDefinition('open_telemetry.traces.samplers.always_off'),
            TraceSamplerEnum::TraceIdRatio => $this->container
                ->getDefinition('open_telemetry.traces.samplers.trace_id_ratio_based')
                ->setArgument('$probability', $sampler['ratio']),
            TraceSamplerEnum::ParentBased => $this->container
                ->getDefinition('open_telemetry.traces.samplers.parent_based')
                ->setArgument('$root', $this->getTraceSamplerDefinition([
                    'type' => $sampler['parent'],
                ])),
        };
    }

    /**
     * @param array{
     *     type: string,
     *     processors?: string[]
     * } $provider
     *
     * @return array{
     *     factory: class-string<TracerProviderFactoryInterface>,
     *     class: class-string<TracerProviderInterface>,
     *     processors: ?Reference[],
     * }
     */
    private function getTraceProviderOptions(array $provider): array
    {
        $providerEnum = TraceProviderEnum::from($provider['type']);
        $options = [
            'factory' => $providerEnum->getFactoryClass(),
            'class' => $providerEnum->getClass(),
            'processors' => [],
        ];

        if (TraceProviderEnum::Default === $providerEnum) {
            if (!isset($provider['processors']) || 0 === count($provider['processors'])) {
                throw new \InvalidArgumentException('Processors are not set or empty');
            }
            $options['processors'] = array_map(
                fn (string $processor) => new Reference(sprintf('open_telemetry.traces.processors.%s', $processor)),
                $provider['processors']
            );
        }

        return $options;
    }

    /**
     * @param array{
     *     name?: string,
     *     version?: string,
     *     provider: string
     * } $tracer
     */
    private function loadTraceTracer(string $name, array $tracer): void
    {
        $tracerId = sprintf('open_telemetry.traces.tracers.%s', $name);

        $this->container
            ->setDefinition($tracerId, new ChildDefinition('open_telemetry.traces.tracer'))
            ->setPublic(true)
            ->setFactory([
                new Reference(sprintf('open_telemetry.traces.providers.%s', $tracer['provider'])),
                'getTracer',
            ])
            ->setArguments([
                $tracer['name'] ?? $this->container->getParameter('open_telemetry.bundle.name'),
                $tracer['version'] ?? $this->container->getParameter('open_telemetry.bundle.version'),
            ]);
    }
}
