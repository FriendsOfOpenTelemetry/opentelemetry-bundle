<?php

namespace GaelReyrol\OpenTelemetryBundle\DependencyInjection;

use GaelReyrol\OpenTelemetryBundle\Factory\SpanExporter\ConsoleSpanExporterFactory;
use GaelReyrol\OpenTelemetryBundle\Factory\SpanExporter\InMemorySpanExporterFactory;
use GaelReyrol\OpenTelemetryBundle\Factory\SpanExporter\OtlpSpanExporterFactory;
use GaelReyrol\OpenTelemetryBundle\Factory\SpanExporter\SpanExporterFactoryInterface;
use GaelReyrol\OpenTelemetryBundle\Factory\SpanExporter\ZipkinSpanExporterFactory;
use GaelReyrol\OpenTelemetryBundle\Factory\SpanProcessor\NoopSpanProcessorFactory;
use GaelReyrol\OpenTelemetryBundle\Factory\SpanProcessor\SimpleSpanProcessorFactory;
use GaelReyrol\OpenTelemetryBundle\Factory\SpanProcessor\SpanProcessorFactoryInterface;
use GaelReyrol\OpenTelemetryBundle\Factory\TracerProvider\NoopTracerProviderFactory;
use GaelReyrol\OpenTelemetryBundle\Factory\TracerProvider\TracerProviderFactory;
use GaelReyrol\OpenTelemetryBundle\Factory\TracerProvider\TracerProviderFactoryInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\MultiSpanProcessor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\HttpKernel\HttpKernel;

/**
 * @phpstan-type ComponentInstrumentationOptions array{enabled: bool, tracer?: string}
 */
final class OpenTelemetryExtension extends ConfigurableExtension
{
    /** @phpstan-ignore-next-line */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(dirname(__DIR__).'/Resources/config'));
        $loader->load('services.php');

        $this->loadService($mergedConfig['service'], $container);
        $this->loadTraces($mergedConfig['traces'], $container);

        $this->loadHttpKernelInstrumentation($mergedConfig['instrumentation']['http_kernel'], $container);
        $this->loadConsoleInstrumentation($mergedConfig['instrumentation']['console'], $container);
    }

    /**
     * @param array{
     *     namespace: string,
     *     name: string,
     *     version: string,
     *     environment: string
     * } $config
     */
    private function loadService(array $config, ContainerBuilder $container): void
    {
        $container->setParameter('open_telemetry.service.namespace', $config['namespace']);
        $container->setParameter('open_telemetry.service.name', $config['name']);
        $container->setParameter('open_telemetry.service.version', $config['version']);
        $container->setParameter('open_telemetry.service.environment', $config['environment']);
    }

    /**
     * @phpstan-param ComponentInstrumentationOptions $config
     */
    private function loadHttpKernelInstrumentation(array $config, ContainerBuilder $container): void
    {
        if (false === $config['enabled']) {
            return;
        }

        if (!class_exists(HttpKernel::class)) {
            throw new \LogicException('To configure the HttpKernel instrumentation, you must first install the symfony/http-kernel package.');
        }

        $definition = $container->getDefinition('open_telemetry.instrumentation.http_kernel.event_subscriber')->addTag('kernel.event_subscriber');

        if (isset($config['tracer'])) {
            $definition->setArgument('tracer', new Reference(sprintf('open_telemetry.traces.tracers.%s', $config['tracer'])));
        }
    }

    /**
     * @phpstan-param ComponentInstrumentationOptions $config
     */
    private function loadConsoleInstrumentation(array $config, ContainerBuilder $container): void
    {
        if (false === $config['enabled']) {
            return;
        }

        if (!class_exists(Application::class)) {
            throw new \LogicException('To configure the Console instrumentation, you must first install the symfony/console package.');
        }

        $definition = $container->getDefinition('open_telemetry.instrumentation.console.event_subscriber')->addTag('kernel.event_subscriber');

        if (isset($config['tracer'])) {
            $definition->setArgument('tracer', new Reference(sprintf('open_telemetry.traces.tracers.%s', $config['tracer'])));
        }
    }

    /**
     * @param array{
     *     enabled: bool,
     *     default_tracer: string,
     *     tracers: array<string, mixed>,
     *     exporters: array<string, mixed>,
     *     processors: array<string, mixed>,
     *     providers: array<string, mixed>
     * } $config
     */
    private function loadTraces(array $config, ContainerBuilder $container): void
    {
        if (false === $config['enabled']) {
            return;
        }

        foreach ($config['exporters'] as $name => $exporter) {
            $this->loadTraceExporter($name, $exporter, $container);
        }

        foreach ($config['processors'] as $name => $processor) {
            $this->loadTraceProcessor($name, $processor, $container);
        }

        foreach ($config['providers'] as $name => $provider) {
            $this->loadTraceProvider($name, $provider, $container);
        }

        foreach ($config['tracers'] as $name => $tracer) {
            $this->loadTraceTracer($name, $tracer, $container);
        }

        $container->set('open_telemetry.traces.default_tracer', new Reference(sprintf('open_telemetry.traces.tracers.%s', $config['default_tracer'])));
    }

    /**
     * @param array{
     *      type: string,
     *      endpoint: string,
     *      headers: array<string, string>,
     *      format?: string,
     *      compression?: string
     * } $exporter
     */
    private function loadTraceExporter(string $name, array $exporter, ContainerBuilder $container): void
    {
        $exporterId = sprintf('open_telemetry.traces.exporters.%s', $name);
        $options = $this->getTraceExporterOptions($exporter);

        $container
            ->setDefinition($exporterId, new ChildDefinition('open_telemetry.traces.exporter'))
            ->setFactory([$options['factory'], 'createFromOptions'])
            ->setArguments($options);
    }

    /**
     * @param array{
     *     type: string,
     *     endpoint: string,
     *     headers: array<string, string>,
     *     format?: string,
     *     compression?: string
     * } $exporter
     *
     * @return array{
     *     type: TraceExporterEnum,
     *     endpoint: string,
     *     headers: array<string, string>,
     *     format: ?OtlpExporterFormatEnum,
     *     compression: ?OtlpExporterCompressionEnum,
     *     factory: class-string<SpanExporterFactoryInterface>
     * }
     */
    private function getTraceExporterOptions(array $exporter): array
    {
        $options = [
            'type' => TraceExporterEnum::from($exporter['type']),
            'endpoint' => $exporter['endpoint'],
            'headers' => $exporter['headers'],
            'format' => isset($exporter['format']) ? OtlpExporterFormatEnum::from($exporter['format']) : null,
            'compression' => isset($exporter['compression']) ? OtlpExporterCompressionEnum::from($exporter['compression']) : null,
        ];

        if (TraceExporterEnum::Otlp === $options['type'] && null === $options['compression']) {
            $options['compression'] = OtlpExporterCompressionEnum::None;
        }

        if (TraceExporterEnum::Otlp === $options['type'] && null === $options['format']) {
            throw new \InvalidArgumentException(sprintf("Exporter is of type '%s' requires a format", $options['type']->value));
        }

        $options['factory'] = match ($options['type']) {
            TraceExporterEnum::InMemory => InMemorySpanExporterFactory::class,
            TraceExporterEnum::Console => ConsoleSpanExporterFactory::class,
            TraceExporterEnum::Otlp => OtlpSpanExporterFactory::class,
            TraceExporterEnum::Zipkin => ZipkinSpanExporterFactory::class,
        };

        return $options;
    }

    /**
     * @param array{
     *      type: string,
     *      processors?: string[],
     *      exporter?: string
     *  } $processor
     */
    private function loadTraceProcessor(string $name, array $processor, ContainerBuilder $container): void
    {
        $processorId = sprintf('open_telemetry.traces.processors.%s', $name);
        $options = $this->getTraceProcessorOptions($processor);

        $container
            ->setDefinition($processorId, new ChildDefinition('open_telemetry.traces.processor'))
            ->setFactory([$options['factory'] => 'createFromOptions'])
            ->setArguments($options);
    }

    /**
     * @param array{
     *     type: string,
     *     processors?: string[],
     *     exporter?: string
     * } $processor
     *
     * @return array{
     *     processors?: Reference[],
     *     exporter?: Reference,
     *     factory: class-string<SpanProcessorFactoryInterface>
     * }
     */
    private function getTraceProcessorOptions(array $processor): array
    {
        $options = [
            'type' => SpanProcessorEnum::from($processor['type']),
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

        if (SpanProcessorEnum::Multi === $options['type']) {
            $options['processors'] = array_map(
                fn (string $processor) => new Reference(sprintf('open_telemetry.traces.processors.%s', $processor)),
                $processor['processors'],
            );
        }

        if (SpanProcessorEnum::Simple === $options['type']) {
            $options['exporter'] = new Reference(sprintf('open_telemetry.traces.processors.%s', $processor['exporter']));
        }

        $options['factory'] = match ($options['type']) {
            SpanProcessorEnum::Noop => NoopSpanProcessorFactory::class,
            SpanProcessorEnum::Simple => SimpleSpanProcessorFactory::class,
            SpanProcessorEnum::Multi => MultiSpanProcessor::class,
            // SpanProcessorEnum::Batch => BatchSpanProcessor::class,
        };

        return $options;
    }

    /**
     * @param array{
     *     type: string,
     *     sampler: array{type: string, ratio?: float, parent?: string},
     *     processors: string[]
     * } $provider
     */
    private function loadTraceProvider(string $name, array $provider, ContainerBuilder $container): void
    {
        $providerId = sprintf('open_telemetry.traces.providers.%s', $name);
        $options = $this->getTraceProviderOptions($provider);

        $options['sampler'] = $this->getTraceSamplerDefinition($provider['sampler'], $container);

        $container
            ->setDefinition($providerId, new ChildDefinition('open_telemetry.traces.provider'))
            ->setFactory([$options['factory'], 'createFromOptions'])
            ->setArguments($options);
    }

    /**
     * @param array{type: string, ratio?: float, parent?: string} $sampler
     */
    private function getTraceSamplerDefinition(array $sampler, ContainerBuilder $container): Definition
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
            TraceSamplerEnum::AlwaysOn => $container->getDefinition('open_telemetry.traces.samplers.always_on'),
            TraceSamplerEnum::AlwaysOff => $container->getDefinition('open_telemetry.traces.samplers.always_off'),
            TraceSamplerEnum::TraceIdRatio => $container
                ->getDefinition('open_telemetry.traces.samplers.trace_id_ratio_based')
                ->setArgument('probability', $sampler['ratio']),
            TraceSamplerEnum::ParentBased => $container
                ->getDefinition('open_telemetry.traces.samplers.parent_based')
                ->setArgument('root', $this->getTraceSamplerDefinition([
                    'type' => $sampler['parent'],
                ], $container)),
        };
    }

    /**
     * @param array{
     *     type: string,
     *     processors: string[]
     * } $provider
     *
     * @return array{
     *     type: TraceProviderEnum,
     *     processors: Reference[],
     *     factory: class-string<TracerProviderFactoryInterface>
     * }
     */
    private function getTraceProviderOptions(array $provider): array
    {
        $options = [
            'type' => TraceProviderEnum::from($provider['type']),
            'processors' => array_map(
                fn (string $processor) => new Reference(sprintf('open_telemetry.traces.processors.%s', $processor)),
                $provider['processors']
            ),
        ];

        $options['factory'] = match ($options['type']) {
            TraceProviderEnum::Default => TracerProviderFactory::class,
            TraceProviderEnum::Noop => NoopTracerProviderFactory::class,
        };

        return $options;
    }

    /**
     * @param array{
     *     name?: string,
     *     version?: string,
     *     provider: string
     * } $tracer
     */
    private function loadTraceTracer(string $name, array $tracer, ContainerBuilder $container): void
    {
        $tracerId = sprintf('open_telemetry.traces.tracers.%s', $name);

        $container
            ->setDefinition($tracerId, new ChildDefinition('open_telemetry.traces.tracer'))
            ->setPublic(true)
            ->setConfigurator([
                new Reference(sprintf('open_telemetry.traces.providers.%s', $tracer['provider'])),
                'getTracer',
            ])
            ->setArguments([
                $tracer['name'] ?? $container->getParameter('open_telemetry.bundle.name'),
                $tracer['version'] ?? $container->getParameter('open_telemetry.bundle.version'),
            ]);
    }
}
