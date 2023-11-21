<?php

namespace GaelReyrol\OpenTelemetryBundle\DependencyInjection;

use GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Factory\Traces\SpanExporter\ConsoleSpanExporterFactory;
use GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Factory\Traces\SpanExporter\InMemorySpanExporterFactory;
use GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Factory\Traces\SpanExporter\OtlpSpanExporterFactory;
use GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Factory\Traces\SpanExporter\SpanExporterFactoryInterface;
use GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Factory\Traces\SpanExporter\ZipkinSpanExporterFactory;
use GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Factory\Traces\SpanProcessor\MultiSpanProcessorFactory;
use GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Factory\Traces\SpanProcessor\NoopSpanProcessorFactory;
use GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Factory\Traces\SpanProcessor\SimpleSpanProcessorFactory;
use GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Factory\Traces\SpanProcessor\SpanProcessorFactoryInterface;
use GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Factory\Traces\TracerProvider\NoopTracerProviderFactory;
use GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Factory\Traces\TracerProvider\TracerProviderFactory;
use GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Factory\Traces\TracerProvider\TracerProviderFactoryInterface;
use GaelReyrol\OpenTelemetryBundle\OpenTelemetry\OtlpExporterCompressionEnum;
use GaelReyrol\OpenTelemetryBundle\OpenTelemetry\OtlpExporterFormatEnum;
use GaelReyrol\OpenTelemetryBundle\OpenTelemetry\SpanProcessorEnum;
use GaelReyrol\OpenTelemetryBundle\OpenTelemetry\TraceExporterEnum;
use GaelReyrol\OpenTelemetryBundle\OpenTelemetry\TraceProviderEnum;
use GaelReyrol\OpenTelemetryBundle\OpenTelemetry\TraceSamplerEnum;
use OpenTelemetry\Contrib\Otlp\SpanExporter as OtlpSpanExporter;
use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinSpanExporter;
use OpenTelemetry\SDK\Trace\NoopTracerProvider;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporter;
use OpenTelemetry\SDK\Trace\SpanExporter\InMemoryExporter;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\MultiSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\NoopSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SDK\Trace\TracerProviderInterface;
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
 * @phpstan-type ComponentInstrumentationOptions array{enabled: bool, default_tracer?: string, request_headers?: string[], response_headers?: string[]}
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

        $this->loadHttpKernelInstrumentation($mergedConfig['instrumentation']['http_kernel'], $mergedConfig['traces'], $container);
        $this->loadConsoleInstrumentation($mergedConfig['instrumentation']['console'], $mergedConfig['traces'], $container);
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
     *
     * @param array{default_tracer: string} $tracesConfig
     */
    private function loadHttpKernelInstrumentation(array $config, array $tracesConfig, ContainerBuilder $container): void
    {
        if (false === $config['enabled']) {
            return;
        }

        if (!class_exists(HttpKernel::class)) {
            throw new \LogicException('To configure the HttpKernel instrumentation, you must first install the symfony/http-kernel package.');
        }

        $definition = $container
            ->getDefinition('open_telemetry.instrumentation.http_kernel.event_subscriber')
            ->setArgument('$requestHeaders', $config['request_headers'])
            ->setArgument('$responseHeaders', $config['response_headers'])
            ->addTag('kernel.event_subscriber');

        if (isset($config['default_tracer'])) {
            $definition->setArgument('$tracer', new Reference(sprintf('open_telemetry.traces.tracers.%s', $config['default_tracer'])));
        } else {
            $definition->setArgument('$tracer', new Reference(sprintf('open_telemetry.traces.tracers.%s', $tracesConfig['default_tracer'])));
        }
    }

    /**
     * @phpstan-param ComponentInstrumentationOptions $config
     *
     * @param array{default_tracer: string} $tracesConfig
     */
    private function loadConsoleInstrumentation(array $config, array $tracesConfig, ContainerBuilder $container): void
    {
        if (false === $config['enabled']) {
            return;
        }

        if (!class_exists(Application::class)) {
            throw new \LogicException('To configure the Console instrumentation, you must first install the symfony/console package.');
        }

        $definition = $container->getDefinition('open_telemetry.instrumentation.console.event_subscriber')->addTag('kernel.event_subscriber');

        if (isset($config['default_tracer'])) {
            $definition->setArgument('$tracer', new Reference(sprintf('open_telemetry.traces.tracers.%s', $config['default_tracer'])));
        } else {
            $definition->setArgument('$tracer', new Reference(sprintf('open_telemetry.traces.tracers.%s', $tracesConfig['default_tracer'])));
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
            ->setClass($options['class'])
            ->setFactory([$options['factory'], 'create'])
            ->setArguments([
                '$endpoint' => $options['endpoint'],
                '$headers' => $options['headers'],
                '$format' => $options['format'],
                '$compression' => $options['compression'],
            ]);
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
     *     factory: class-string<SpanExporterFactoryInterface>,
     *     class: class-string<SpanExporterInterface>
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

        $options['class'] = match ($options['type']) {
            TraceExporterEnum::InMemory => InMemoryExporter::class,
            TraceExporterEnum::Console => ConsoleSpanExporter::class,
            TraceExporterEnum::Otlp => OtlpSpanExporter::class,
            TraceExporterEnum::Zipkin => ZipkinSpanExporter::class,
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

        $args = [];

        if (isset($options['processors'])) {
            $args['$processors'] = $options['processors'];
        }

        if (isset($options['exporter'])) {
            $args['$exporter'] = $options['exporter'];
        }

        $container
            ->setDefinition($processorId, new ChildDefinition('open_telemetry.traces.processor'))
            ->setClass($options['class'])
            ->setFactory([$options['factory'], 'create'])
            ->setArguments($args);
    }

    /**
     * @param array{
     *     type: string,
     *     processors?: string[],
     *     exporter?: string
     * } $processor
     *
     * @return array{
     *     type: SpanProcessorEnum,
     *     processors?: Reference[],
     *     exporter?: Reference,
     *     factory: class-string<SpanProcessorFactoryInterface>,
     *     class: class-string<SpanProcessorInterface>
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
            $options['exporter'] = new Reference(sprintf('open_telemetry.traces.exporters.%s', $processor['exporter']));
        }

        $options['factory'] = match ($options['type']) {
            SpanProcessorEnum::Noop => NoopSpanProcessorFactory::class,
            SpanProcessorEnum::Simple => SimpleSpanProcessorFactory::class,
            SpanProcessorEnum::Multi => MultiSpanProcessorFactory::class,
            // SpanProcessorEnum::Batch => BatchSpanProcessorFactory::class,
        };

        $options['class'] = match ($options['type']) {
            SpanProcessorEnum::Noop => NoopSpanProcessor::class,
            SpanProcessorEnum::Simple => SimpleSpanProcessor::class,
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

        $container
            ->setDefinition($providerId, new ChildDefinition('open_telemetry.traces.provider'))
            ->setClass($options['class'])
            ->setFactory([$options['factory'], 'create'])
            ->setArguments([
                '$sampler' => $this->getTraceSamplerDefinition($provider['sampler'], $container),
                '$processors' => $options['processors'],
            ]);
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
                ->setArgument('$probability', $sampler['ratio']),
            TraceSamplerEnum::ParentBased => $container
                ->getDefinition('open_telemetry.traces.samplers.parent_based')
                ->setArgument('$root', $this->getTraceSamplerDefinition([
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
     *     factory: class-string<TracerProviderFactoryInterface>,
     *     class: class-string<TracerProviderInterface>
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

        $options['class'] = match ($options['type']) {
            TraceProviderEnum::Default => TracerProvider::class,
            TraceProviderEnum::Noop => NoopTracerProvider::class,
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
            ->setFactory([
                new Reference(sprintf('open_telemetry.traces.providers.%s', $tracer['provider'])),
                'getTracer',
            ])
            ->setArguments([
                $tracer['name'] ?? $container->getParameter('open_telemetry.bundle.name'),
                $tracer['version'] ?? $container->getParameter('open_telemetry.bundle.version'),
            ]);
    }
}
