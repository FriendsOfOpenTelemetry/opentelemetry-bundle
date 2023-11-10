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
use GaelReyrol\OpenTelemetryBundle\Factory\TracerProvider\TracerProviderFactoryInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\MultiSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProviderFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\HttpKernel\HttpKernel;

/**
 * @phpstan-type ComponentInstrumentationOptions array{enabled: bool, tracing_provider?: string}
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

        $this->loadHttpKernelInstrumentation($mergedConfig['instrumentation']['kernel'], $container);
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

        if (isset($config['tracing_provider'])) {
            $definition->setArgument('tracerProvider', new Reference(sprintf('open_telemetry.traces.provider.%s', $config['tracing_provider'])));
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

        if (isset($config['tracing_provider'])) {
            $definition->setArgument('tracerProvider', new Reference(sprintf('open_telemetry.traces.provider.%s', $config['tracing_provider'])));
        }
    }

    /**
     * @param array{
     *     enabled: bool,
     *     default_provider: string,
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

        $container->set('open_telemetry.traces.default_provider', new Reference(sprintf('open_telemetry.traces.providers.%s', $config['default_provider'])));
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
            'format' => OtlpExporterFormatEnum::tryFrom($exporter['format']),
            'compression' => OtlpExporterCompressionEnum::tryFrom($exporter['compression']),
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
     *     sampler: string,
     *     processors: string[]
     * } $provider
     */
    private function loadTraceProvider(string $name, array $provider, ContainerBuilder $container): void
    {
        $providerId = sprintf('open_telemetry.traces.provider.%s', $name);
        $options = $this->getTraceProviderOptions($provider);

        $container
            ->setDefinition($providerId, new ChildDefinition('open_telemetry.traces.provider'))
            ->setFactory([$options['factory'], 'createFromOptions'])
            ->setArguments($options);
    }

    /**
     * @param array{
     *     type: string,
     *     sampler: string,
     *     processors: string[]
     * } $provider
     *
     * @return array{
     *     type: TraceProviderEnum,
     *     processors: Reference[],
     *     sampler: Reference,
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

        $options['sampler'] = match (TraceSamplerEnum::from($provider['sampler'])) {
            TraceSamplerEnum::AlwaysOn => new Reference('open_telemetry.traces.samplers.always_on'),
            TraceSamplerEnum::AlwaysOff => new Reference('open_telemetry.traces.samplers.always_off'),
            TraceSamplerEnum::TraceIdRatio => new Reference('open_telemetry.traces.samplers.trace_id_ratio_based'),
            TraceSamplerEnum::ParentBased => new Reference('open_telemetry.traces.samplers.parent_based'),
        };

        $options['factory'] = match ($options['type']) {
            TraceProviderEnum::Default => TracerProviderFactory::class,
            TraceProviderEnum::Noop => NoopTracerProviderFactory::class,
        };

        return $options;
    }
}
