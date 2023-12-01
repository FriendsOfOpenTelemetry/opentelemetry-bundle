<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter\InMemoryLogExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter\LogExporterEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter\LogExporterFactoryInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter\NoopLogExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter\OtlpLogExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider\LoggerProviderEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider\LoggerProviderFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider\LoggerProviderFactoryInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider\NoopLoggerProviderFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor\LogProcessorEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor\LogProcessorFactoryInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor\MultiLogProcessorFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor\SimpleLogProcessorFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider\ExemplarFilterEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider\MeterProviderEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider\MeterProviderFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider\MeterProviderFactoryInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider\NoopMeterProviderFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\ConsoleMetricExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\InMemoryMetricExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\MetricExporterEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\MetricExporterFactoryInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\MetricTemporalityEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\NoopMetricExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\OtlpMetricExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\OtlpExporterCompressionEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\OtlpExporterFormatEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\SpanProcessorEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\ConsoleSpanExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\InMemorySpanExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\OtlpSpanExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\SpanExporterFactoryInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\TraceExporterEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\ZipkinSpanExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor\MultiSpanProcessorFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor\NoopSpanProcessorFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor\SimpleSpanProcessorFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor\SpanProcessorFactoryInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TracerProvider\NoopTracerProviderFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TracerProvider\TraceProviderEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TracerProvider\TracerProviderFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TracerProvider\TracerProviderFactoryInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TracerProvider\TraceSamplerEnum;
use Monolog\Level;
use OpenTelemetry\Contrib\Logs\Monolog\Handler;
use OpenTelemetry\Contrib\Otlp\LogsExporter as DefautLogExporter;
use OpenTelemetry\Contrib\Otlp\MetricExporter;
use OpenTelemetry\Contrib\Otlp\SpanExporter as OtlpSpanExporter;
use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinSpanExporter;
use OpenTelemetry\SDK\Logs\Exporter\ConsoleExporter;
use OpenTelemetry\SDK\Logs\Exporter\InMemoryExporter as InMemoryLogExporter;
use OpenTelemetry\SDK\Logs\Exporter\NoopExporter as NoopLogExporter;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\LoggerProviderInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\NoopLoggerProvider;
use OpenTelemetry\SDK\Logs\Processor\MultiLogRecordProcessor;
use OpenTelemetry\SDK\Logs\Processor\NoopLogRecordProcessor;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Metrics\MetricExporter\NoopMetricExporter;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Metrics\NoopMeterProvider;
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
 * @phpstan-type ComponentInstrumentationOptions array{enabled: bool, tracer?: string, request_headers?: string[], response_headers?: string[], meter?: string}
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
        $this->loadMetrics($mergedConfig['metrics'], $container);
        $this->loadLogs($mergedConfig['logs'], $container);
        $this->loadMonologHandlers($mergedConfig['logs'], $container);

        $this->loadHttpKernelInstrumentation($mergedConfig, $container);
        $this->loadConsoleInstrumentation($mergedConfig, $container);
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

    /** @phpstan-ignore-next-line */
    private function loadHttpKernelInstrumentation(array $config, ContainerBuilder $container): void
    {
        $httpKernelConfig = $config['instrumentation']['http_kernel'];
        if (false === $httpKernelConfig['enabled']) {
            return;
        }

        if (!class_exists(HttpKernel::class)) {
            throw new \LogicException('To configure the HttpKernel instrumentation, you must first install the symfony/http-kernel package.');
        }

        $trace = $container
            ->getDefinition('open_telemetry.instrumentation.http_kernel.trace.event_subscriber')
            ->setArgument('$requestHeaders', $httpKernelConfig['tracing']['request_headers'])
            ->setArgument('$responseHeaders', $httpKernelConfig['tracing']['response_headers'])
            ->addTag('kernel.event_subscriber');

        if (isset($httpKernelConfig['tracing']['tracer'])) {
            $trace->setArgument('$tracer', new Reference(sprintf('open_telemetry.traces.tracers.%s', $httpKernelConfig['tracing']['tracer'])));
        } else {
            $defaultTracer = $config['traces']['default_tracer'] ?? array_key_first($config['traces']['tracers']);
            $trace->setArgument('$tracer', new Reference(sprintf('open_telemetry.traces.tracers.%s', $defaultTracer)));
        }

        $metric = $container->getDefinition('open_telemetry.instrumentation.http_kernel.metric.event_subscriber')->addTag('kernel.event_subscriber');
        if (isset($httpKernelConfig['metering']['meter'])) {
            $metric->setArgument('$meter', new Reference(sprintf('open_telemetry.metrics.meters.%s', $httpKernelConfig['metering']['meter'])));
            if (!isset($config['metrics']['meters'][$httpKernelConfig['meter']]['provider'])) {
                throw new \InvalidArgumentException('Meter provider has not found');
            }
            $meterProvider = $config['metrics']['meters'][$httpKernelConfig['metering']['meter']]['provider'];
            $metric->setArgument('$meterProvider', new Reference(sprintf('open_telemetry.metrics.providers.%s', $meterProvider)));
        } else {
            $defaultMeter = $config['metrics']['default_meter'] ?? array_key_first($config['metrics']['meters']);
            $metric->setArgument('$meter', new Reference(sprintf('open_telemetry.metrics.meters.%s', $defaultMeter)));
            if (!isset($config['metrics']['meters'][$defaultMeter]['provider'])) {
                throw new \InvalidArgumentException('Meter provider has not found');
            }
            $meterProvider = $config['metrics']['meters'][$defaultMeter]['provider'];
            $metric->setArgument('$meterProvider', new Reference(sprintf('open_telemetry.metrics.providers.%s', $meterProvider)));
        }
    }

    /** @phpstan-ignore-next-line */
    private function loadConsoleInstrumentation(array $config, ContainerBuilder $container): void
    {
        $consoleConfig = $config['instrumentation']['console'];
        if (false === $consoleConfig['enabled']) {
            return;
        }

        if (!class_exists(Application::class)) {
            throw new \LogicException('To configure the Console instrumentation, you must first install the symfony/console package.');
        }

        $trace = $container->getDefinition('open_telemetry.instrumentation.console.trace.event_subscriber')->addTag('kernel.event_subscriber');
        if (isset($consoleConfig['tracing']['tracer'])) {
            $trace->setArgument('$tracer', new Reference(sprintf('open_telemetry.traces.tracers.%s', $consoleConfig['tracing']['tracer'])));
        } else {
            $defaultTracer = $config['traces']['default_tracer'] ?? array_key_first($config['traces']['tracers']);
            $trace->setArgument('$tracer', new Reference(sprintf('open_telemetry.traces.tracers.%s', $defaultTracer)));
        }

        $metric = $container->getDefinition('open_telemetry.instrumentation.console.metric.event_subscriber')->addTag('kernel.event_subscriber');
        if (isset($consoleConfig['metering']['meter'])) {
            $metric->setArgument('$meter', new Reference(sprintf('open_telemetry.metrics.meters.%s', $config['meter'])));
            if (!isset($config['metrics']['meters'][$consoleConfig['metering']['meter']]['provider'])) {
                throw new \InvalidArgumentException('Meter provider has not found');
            }
            $meterProvider = $config['metrics']['meters'][$consoleConfig['metering']['meter']]['provider'];
            $metric->setArgument('$meterProvider', new Reference(sprintf('open_telemetry.metrics.providers.%s', $meterProvider)));
        } else {
            $defaultMeter = $config['metrics']['default_meter'] ?? array_key_first($config['metrics']['meters']);
            $metric->setArgument('$meter', new Reference(sprintf('open_telemetry.metrics.meters.%s', $defaultMeter)));
            if (!isset($config['metrics']['meters'][$defaultMeter]['provider'])) {
                throw new \InvalidArgumentException('Meter provider has not found');
            }
            $meterProvider = $config['metrics']['meters'][$defaultMeter]['provider'];
            $metric->setArgument('$meterProvider', new Reference(sprintf('open_telemetry.metrics.providers.%s', $meterProvider)));
        }
    }

    /**
     * @param array{
     *     default_tracer?: string,
     *     tracers: array<string, mixed>,
     *     exporters: array<string, mixed>,
     *     processors: array<string, mixed>,
     *     providers: array<string, mixed>
     * } $config
     */
    private function loadTraces(array $config, ContainerBuilder $container): void
    {
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

        $defaultTracer = $config['default_tracer'] ?? null;
        if (0 < count($config['tracers'])) {
            $defaultTracer = array_key_first($config['tracers']);
        }

        if (null !== $defaultTracer) {
            $container->set('open_telemetry.traces.default_tracer', new Reference(sprintf('open_telemetry.traces.tracers.%s', $defaultTracer)));
        }
    }

    /**
     * @param array{
     *      type: string,
     *      endpoint?: string,
     *      headers?: array<string, string>,
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
     *     endpoint?: string,
     *     headers?: array<string, string>,
     *     format?: string,
     *     compression?: string
     * } $exporter
     *
     * @return array{
     *     type: TraceExporterEnum,
     *     endpoint: ?string,
     *     headers: ?array<string, string>,
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
            'endpoint' => $exporter['endpoint'] ?? null,
            'headers' => $exporter['headers'] ?? [],
            'format' => isset($exporter['format']) ? OtlpExporterFormatEnum::from($exporter['format']) : null,
            'compression' => isset($exporter['compression']) ? OtlpExporterCompressionEnum::from($exporter['compression']) : null,
        ];

        if (TraceExporterEnum::Otlp === $options['type'] && null === $options['format']) {
            $options['format'] = OtlpExporterFormatEnum::Json;
        }

        if (TraceExporterEnum::Otlp === $options['type'] && null === $options['compression']) {
            $options['compression'] = OtlpExporterCompressionEnum::None;
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

        $container
            ->setDefinition($processorId, new ChildDefinition('open_telemetry.traces.processor'))
            ->setClass($options['class'])
            ->setFactory([$options['factory'], 'create'])
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
     *     type: SpanProcessorEnum,
     *     processors: ?Reference[],
     *     exporter: ?Reference,
     *     factory: class-string<SpanProcessorFactoryInterface>,
     *     class: class-string<SpanProcessorInterface>
     * }
     */
    private function getTraceProcessorOptions(array $processor): array
    {
        $options = [
            'type' => SpanProcessorEnum::from($processor['type']),
            'processors' => null,
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

        if (SpanProcessorEnum::Multi === $options['type']) {
            if (!isset($processor['processors']) || 0 === count($processor['processors'])) {
                throw new \InvalidArgumentException('Processors are not set or empty');
            }
            $options['processors'] = array_map(
                fn (string $processor) => new Reference(sprintf('open_telemetry.traces.processors.%s', $processor)),
                $processor['processors'],
            );
        }

        if (SpanProcessorEnum::Simple === $options['type']) {
            if (!isset($processor['exporter'])) {
                throw new \InvalidArgumentException('Exporter is not set');
            }
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
     *     sampler?: array{type: string, ratio?: float, parent?: string},
     *     processors?: string[]
     * } $provider
     */
    private function loadTraceProvider(string $name, array $provider, ContainerBuilder $container): void
    {
        $providerId = sprintf('open_telemetry.traces.providers.%s', $name);
        $options = $this->getTraceProviderOptions($provider);

        $sampler = isset($provider['sampler']) ? $this->getTraceSamplerDefinition($provider['sampler'], $container) : $container->getDefinition('open_telemetry.traces.samplers.always_on');

        $container
            ->setDefinition($providerId, new ChildDefinition('open_telemetry.traces.provider'))
            ->setClass($options['class'])
            ->setFactory([$options['factory'], 'create'])
            ->setArguments([
                '$sampler' => $sampler,
                '$processors' => $options['processors'],
            ]);
    }

    /**
     * @param array{type: string, ratio?: float, parent?: string} $sampler
     */
    private function getTraceSamplerDefinition(?array $sampler, ContainerBuilder $container): Definition
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
     *     processors?: string[]
     * } $provider
     *
     * @return array{
     *     type: TraceProviderEnum,
     *     processors: ?Reference[],
     *     factory: class-string<TracerProviderFactoryInterface>,
     *     class: class-string<TracerProviderInterface>
     * }
     */
    private function getTraceProviderOptions(array $provider): array
    {
        $options = [
            'type' => TraceProviderEnum::from($provider['type']),
            'processors' => null,
        ];

        if (TraceProviderEnum::Default === $options['type']) {
            if (!isset($provider['processors']) || 0 === count($provider['processors'])) {
                throw new \InvalidArgumentException('Processors are not set or empty');
            }
            $options['processors'] = array_map(
                fn (string $processor) => new Reference(sprintf('open_telemetry.traces.processors.%s', $processor)),
                $provider['processors']
            );
        }

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

    /**
     * @param array{
     *     default_meter?: string,
     *     meters: array<string, mixed>,
     *     exporters: array<string, mixed>,
     *     providers: array<string, mixed>
     * } $config
     */
    private function loadMetrics(array $config, ContainerBuilder $container): void
    {
        foreach ($config['exporters'] as $name => $exporter) {
            $this->loadMetricExporter($name, $exporter, $container);
        }

        foreach ($config['providers'] as $name => $provider) {
            $this->loadMetricProvider($name, $provider, $container);
        }

        foreach ($config['meters'] as $name => $meter) {
            $this->loadMetricMeter($name, $meter, $container);
        }

        $defaultMeter = $config['default_meter'] ?? null;
        if (0 < count($config['meters'])) {
            $defaultMeter = array_key_first($config['meters']);
        }

        if (null !== $defaultMeter) {
            $container->set('open_telemetry.metrics.default_meter', new Reference(sprintf('open_telemetry.metrics.meters.%s', $defaultMeter)));
        }
    }

    /**
     * @param array{
     *     type: string,
     *     endpoint?: string,
     *     format?: string,
     *     headers?: array<string, string>,
     *     compression?: string,
     *     temporality?: string,
     * } $exporter
     */
    private function loadMetricExporter(string $name, array $exporter, ContainerBuilder $container): void
    {
        $exporterId = sprintf('open_telemetry.metrics.exporters.%s', $name);
        $options = $this->getMetricExporterOptions($exporter);

        $container
            ->setDefinition($exporterId, new ChildDefinition('open_telemetry.metrics.exporter'))
            ->setClass($options['class'])
            ->setFactory([$options['factory'], 'create'])
            ->setArguments([
                '$endpoint' => $options['endpoint'],
                '$format' => $options['format'],
                '$headers' => $options['headers'],
                '$compression' => $options['compression'],
                '$temporality' => $options['temporality'],
            ]);
    }

    /**
     * @param array{
     *     type: string,
     *     endpoint?: string,
     *     format?: string,
     *     headers?: array<string, string>,
     *     compression?: string,
     *     temporality?: string,
     * } $exporter
     *
     * @return array{
     *     type: MetricExporterEnum,
     *     endpoint: ?string,
     *     headers: ?array<string, string>,
     *     format: ?OtlpExporterFormatEnum,
     *     compression: ?OtlpExporterCompressionEnum,
     *     temporality: ?MetricTemporalityEnum,
     *     factory: class-string<MetricExporterFactoryInterface>,
     *     class: class-string<MetricExporterInterface>
     * }
     */
    private function getMetricExporterOptions(array $exporter): array
    {
        $options = [
            'type' => MetricExporterEnum::from($exporter['type']),
            'endpoint' => $exporter['endpoint'] ?? null,
            'headers' => $exporter['headers'] ?? [],
            'format' => isset($exporter['format']) ? OtlpExporterFormatEnum::from($exporter['format']) : null,
            'compression' => isset($exporter['compression']) ? OtlpExporterCompressionEnum::from($exporter['compression']) : null,
            'temporality' => isset($exporter['temporality']) ? MetricTemporalityEnum::from($exporter['temporality']) : null,
        ];

        $options['factory'] = match ($options['type']) {
            MetricExporterEnum::Noop => NoopMetricExporterFactory::class,
            MetricExporterEnum::Otlp => OtlpMetricExporterFactory::class,
            MetricExporterEnum::InMemory => InMemoryMetricExporterFactory::class,
            MetricExporterEnum::Console => ConsoleMetricExporterFactory::class,
        };

        $options['class'] = match ($options['type']) {
            MetricExporterEnum::Noop => NoopMetricExporter::class,
            MetricExporterEnum::Otlp => MetricExporter::class,
            MetricExporterEnum::InMemory => InMemoryMetricExporterFactory::class,
            MetricExporterEnum::Console => ConsoleMetricExporterFactory::class,
        };

        return $options;
    }

    /**
     * @param array{
     *     type: string,
     *     exporter?: string,
     *     filter?: string
     * } $provider
     */
    private function loadMetricProvider(string $name, array $provider, ContainerBuilder $container): void
    {
        $providerId = sprintf('open_telemetry.metrics.providers.%s', $name);
        $options = $this->getMetricProviderOptions($provider);

        $container
            ->setDefinition($providerId, new ChildDefinition('open_telemetry.metrics.provider'))
            ->setClass($options['class'])
            ->setFactory([$options['factory'], 'create'])
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
     *     type: MeterProviderEnum,
     *     exporter: ?Reference,
     *     filter: ?Reference,
     *     factory: class-string<MeterProviderFactoryInterface>,
     *     class: class-string<MeterProviderInterface>
     * }
     */
    private function getMetricProviderOptions(array $provider): array
    {
        $options = [
            'type' => MeterProviderEnum::from($provider['type']),
            'exporter' => null,
            'filter' => null,
        ];

        if (MeterProviderEnum::Default === $options['type']) {
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

        $options['factory'] = match ($options['type']) {
            MeterProviderEnum::Default => MeterProviderFactory::class,
            MeterProviderEnum::Noop => NoopMeterProviderFactory::class,
        };

        $options['class'] = match ($options['type']) {
            MeterProviderEnum::Default => MeterProvider::class,
            MeterProviderEnum::Noop => NoopMeterProvider::class,
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
    private function loadMetricMeter(string $name, array $meter, ContainerBuilder $container): void
    {
        $meterId = sprintf('open_telemetry.metrics.meters.%s', $name);

        $container
            ->setDefinition($meterId, new ChildDefinition('open_telemetry.metrics.meter'))
            ->setPublic(true)
            ->setFactory([
                new Reference(sprintf('open_telemetry.metrics.providers.%s', $meter['provider'])),
                'getMeter',
            ])
            ->setArguments([
                $meter['name'] ?? $container->getParameter('open_telemetry.bundle.name'),
                $meter['version'] ?? $container->getParameter('open_telemetry.bundle.version'),
            ]);
    }

    /**
     * @param array{
     *     default_logger?: string,
     *     loggers: array<string, mixed>,
     *     exporters: array<string, mixed>,
     *     processors: array<string, mixed>,
     *     providers: array<string, mixed>
     * } $config
     */
    private function loadLogs(array $config, ContainerBuilder $container): void
    {
        foreach ($config['exporters'] as $name => $exporter) {
            $this->loadLogExporter($name, $exporter, $container);
        }

        foreach ($config['processors'] as $name => $processor) {
            $this->loadLogProcessor($name, $processor, $container);
        }

        foreach ($config['providers'] as $name => $provider) {
            $this->loadLogProvider($name, $provider, $container);
        }

        foreach ($config['loggers'] as $name => $logger) {
            $this->loadLogLogger($name, $logger, $container);
        }

        $defaultLogger = $config['default_logger'] ?? null;
        if (0 < count($config['loggers'])) {
            $defaultLogger = array_key_first($config['loggers']);
        }

        if (null !== $defaultLogger) {
            $container->set('open_telemetry.logs.default_logger', new Reference(sprintf('open_telemetry.logs.loggers.%s', $defaultLogger)));
        }
    }

    /**
     * @param array{
     *     type: string,
     *     endpoint?: string,
     *     format?: string,
     *     headers?: array<string, string>,
     *     compression?: string,
     * } $exporter
     */
    private function loadLogExporter(string $name, array $exporter, ContainerBuilder $container): void
    {
        $exporterId = sprintf('open_telemetry.logs.exporters.%s', $name);
        $options = $this->getLogExporterOptions($exporter);

        $container
            ->setDefinition($exporterId, new ChildDefinition('open_telemetry.logs.exporter'))
            ->setClass($options['class'])
            ->setFactory([$options['factory'], 'create'])
            ->setArguments([
                '$endpoint' => $options['endpoint'],
                '$format' => $options['format'],
                '$headers' => $options['headers'],
                '$compression' => $options['compression'],
            ]);
    }

    /**
     * @param array{
     *     type: string,
     *     endpoint?: string,
     *     format?: string,
     *     headers?: array<string, string>,
     *     compression?: string,
     * } $exporter
     *
     * @return array{
     *     type: LogExporterEnum,
     *     endpoint: ?string,
     *     headers: ?array<string, string>,
     *     format: ?OtlpExporterFormatEnum,
     *     compression: ?OtlpExporterCompressionEnum,
     *     factory: class-string<LogExporterFactoryInterface>,
     *     class: class-string<LogRecordExporterInterface>
     * }
     */
    private function getLogExporterOptions(array $exporter): array
    {
        $options = [
            'type' => LogExporterEnum::from($exporter['type']),
            'endpoint' => $exporter['endpoint'] ?? null,
            'headers' => $exporter['headers'] ?? null,
            'format' => isset($exporter['format']) ? OtlpExporterFormatEnum::from($exporter['format']) : null,
            'compression' => isset($exporter['compression']) ? OtlpExporterCompressionEnum::from($exporter['compression']) : null,
        ];

        $options['factory'] = match ($options['type']) {
            LogExporterEnum::Noop => NoopLogExporterFactory::class,
            LogExporterEnum::Otlp => OtlpLogExporterFactory::class,
            LogExporterEnum::InMemory => InMemoryLogExporterFactory::class,
            LogExporterEnum::Console => ConsoleMetricExporterFactory::class,
        };

        $options['class'] = match ($options['type']) {
            LogExporterEnum::Noop => NoopLogExporter::class,
            LogExporterEnum::Otlp => DefautLogExporter::class,
            LogExporterEnum::InMemory => InMemoryLogExporter::class,
            LogExporterEnum::Console => ConsoleExporter::class,
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
    private function loadLogProcessor(string $name, array $processor, ContainerBuilder $container): void
    {
        $processorId = sprintf('open_telemetry.logs.processors.%s', $name);
        $options = $this->getLogProcessorOptions($processor);

        $container
            ->setDefinition($processorId, new ChildDefinition('open_telemetry.logs.processor'))
            ->setClass($options['class'])
            ->setFactory([$options['factory'], 'create'])
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
     *     type: LogProcessorEnum,
     *     processors: ?Reference[],
     *     exporter: ?Reference,
     *     factory: class-string<LogProcessorFactoryInterface>,
     *     class: class-string<LogRecordProcessorInterface>
     * }
     */
    private function getLogProcessorOptions(array $processor): array
    {
        $options = [
            'type' => LogProcessorEnum::from($processor['type']),
            'processors' => null,
            'exporter' => null,
        ];

        // if (LogProcessorEnum::Batch === $options['type']) {
        //     // TODO: Check batch options
        //     clock: OpenTelemetry\SDK\Common\Time\SystemClock
        //     max_queue_size: 2048
        //     schedule_delay: 5000
        //     export_timeout: 30000
        //     max_export_batch_size: 512
        //     auto_flush: true
        // }

        if (LogProcessorEnum::Multi === $options['type']) {
            if (!isset($processor['processors']) || 0 === count($processor['processors'])) {
                throw new \InvalidArgumentException('Processors are not set or empty');
            }
            $options['processors'] = array_map(
                fn (string $processor) => new Reference(sprintf('open_telemetry.logs.processors.%s', $processor)),
                $processor['processors'],
            );
        }

        if (LogProcessorEnum::Simple === $options['type']) {
            if (!isset($processor['exporter'])) {
                throw new \InvalidArgumentException('Exporter is not set');
            }
            $options['exporter'] = new Reference(sprintf('open_telemetry.logs.exporters.%s', $processor['exporter']));
        }

        $options['factory'] = match ($options['type']) {
            LogProcessorEnum::Noop => NoopLogExporterFactory::class,
            LogProcessorEnum::Simple => SimpleLogProcessorFactory::class,
            LogProcessorEnum::Multi => MultiLogProcessorFactory::class,
            // LogProcessorEnum::Batch => BatchLogRecordProcessorFactory::class,
        };

        $options['class'] = match ($options['type']) {
            LogProcessorEnum::Noop => NoopLogRecordProcessor::class,
            LogProcessorEnum::Simple => SimpleLogRecordProcessor::class,
            LogProcessorEnum::Multi => MultiLogRecordProcessor::class,
            // LogProcessorEnum::Batch => BatchLogRecordProcessor::class,
        };

        return $options;
    }

    /**
     * @param array{
     *     type: string,
     *     processor?: string,
     * } $provider
     */
    private function loadLogProvider(string $name, array $provider, ContainerBuilder $container): void
    {
        $providerId = sprintf('open_telemetry.logs.providers.%s', $name);
        $options = $this->getLoggerProviderOptions($provider);

        $container
            ->setDefinition($providerId, new ChildDefinition('open_telemetry.logs.provider'))
            ->setClass($options['class'])
            ->setFactory([$options['factory'], 'create'])
            ->setArguments([
                '$processor' => $options['processor'],
            ]);
    }

    /**
     * @param array{
     *     type: string,
     *     processor?: string,
     * } $provider
     *
     * @return array{
     *     type: LoggerProviderEnum,
     *     processor: ?Reference,
     *     factory: class-string<LoggerProviderFactoryInterface>,
     *     class: class-string<LoggerProviderInterface>
     * }
     */
    private function getLoggerProviderOptions(array $provider): array
    {
        $options = [
            'type' => LoggerProviderEnum::from($provider['type']),
            'processor' => null,
        ];

        if (LoggerProviderEnum::Default === $options['type']) {
            if (!isset($provider['processor'])) {
                throw new \InvalidArgumentException('Processor is not set');
            }
            $options['processor'] = new Reference(sprintf('open_telemetry.logs.processors.%s', $provider['processor']));
        }

        $options['factory'] = match ($options['type']) {
            LoggerProviderEnum::Default => LoggerProviderFactory::class,
            LoggerProviderEnum::Noop => NoopLoggerProviderFactory::class,
        };

        $options['class'] = match ($options['type']) {
            LoggerProviderEnum::Default => LoggerProvider::class,
            LoggerProviderEnum::Noop => NoopLoggerProvider::class,
        };

        return $options;
    }

    /**
     * @param array{
     *     provider: string,
     *     name?: string,
     *     version?: string,
     * } $logger
     */
    private function loadLogLogger(string $name, array $logger, ContainerBuilder $container): void
    {
        $loggerId = sprintf('open_telemetry.logs.loggers.%s', $name);

        $container
            ->setDefinition($loggerId, new ChildDefinition('open_telemetry.logs.logger'))
            ->setPublic(true)
            ->setFactory([
                new Reference(sprintf('open_telemetry.logs.providers.%s', $logger['provider'])),
                'getLogger',
            ])
            ->setArguments([
                $logger['name'] ?? $container->getParameter('open_telemetry.bundle.name'),
                $logger['version'] ?? $container->getParameter('open_telemetry.bundle.version'),
            ]);
    }

    /**
     * @param array{
     *     default_logger?: string,
     *     monolog: array{enabled: bool, handlers: array<array{handler: string, provider: string, level: string, bubble: bool}>},
     *     loggers: array<string, mixed>,
     *     exporters: array<string, mixed>,
     *     processors: array<string, mixed>,
     *     providers: array<string, mixed>
     * } $config
     */
    private function loadMonologHandlers(array $config, ContainerBuilder $container): void
    {
        if (false === $config['monolog']['enabled']) {
            return;
        }

        if (!class_exists(Handler::class)) {
            throw new \LogicException('To configure the Monolog handler, you must first install the open-telemetry/opentelemetry-logger-monolog package.');
        }

        foreach ($config['monolog']['handlers'] as $name => $handler) {
            $handlerId = sprintf('open_telemetry.logs.monolog.handlers.%s', $name);
            $container
                ->setDefinition($handlerId, new ChildDefinition('open_telemetry.logs.monolog.handler'))
                ->setPublic(true)
                ->setArguments([
                    '$loggerProvider' => new Reference(sprintf('open_telemetry.logs.providers.%s', $handler['provider'])),
                    '$level' => Level::fromName(ucfirst($handler['level'])),
                    '$bubble' => $handler['bubble'],
                ]);
        }
    }
}
