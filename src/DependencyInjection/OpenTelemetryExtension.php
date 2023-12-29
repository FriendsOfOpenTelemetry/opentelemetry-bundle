<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter\LogExporterEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider\LoggerProviderEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider\LoggerProviderFactoryInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor\LogProcessorEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor\LogProcessorFactoryInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider\ExemplarFilterEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider\MeterProviderEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider\MeterProviderFactoryInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\MetricExporterEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\TraceExporterEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor\SpanProcessorEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor\SpanProcessorFactoryInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TracerProvider\TraceProviderEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TracerProvider\TracerProviderFactoryInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TracerProvider\TraceSamplerEnum;
use Monolog\Level;
use OpenTelemetry\Contrib\Logs\Monolog\Handler;
use OpenTelemetry\SDK\Logs\LoggerProviderInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
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
 * @phpstan-type ComponentInstrumentationOptions array{
 *     enabled: bool,
 *     tracer?: string,
 *     request_headers?: string[],
 *     response_headers?: string[],
 *     meter?: string,
 * }
 *
 * @phpstan-import-type ExporterOptions from ExporterOptionsInterface
 */
final class OpenTelemetryExtension extends ConfigurableExtension
{
    public const EXPORTER_OPTIONS = ['format', 'headers', 'compression', 'timeout', 'retry', 'max', 'ca', 'cert', 'key'];
    public const METRIC_EXPORTER_OPTIONS = ['temporality'];

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

        $this->loadHttpKernelTracingInstrumentation($config, $container);
        $this->loadHttpKernelMeteringInstrumentation($config, $container);
    }

    /** @phpstan-ignore-next-line */
    public function loadHttpKernelTracingInstrumentation(array $config, ContainerBuilder $container): void
    {
        $httpKernelConfig = $config['instrumentation']['http_kernel'];
        $tracingHttpKernel = $httpKernelConfig['tracing'];

        if (false === $tracingHttpKernel['enabled']) {
            return;
        }

        $trace = $container
            ->getDefinition('open_telemetry.instrumentation.http_kernel.trace.event_subscriber')
            ->setArgument('$requestHeaders', $tracingHttpKernel['request_headers'])
            ->setArgument('$responseHeaders', $tracingHttpKernel['response_headers'])
            ->addTag('kernel.event_subscriber');

        if (isset($tracingHttpKernel['tracer'])) {
            $trace->setArgument('$tracer', new Reference(sprintf('open_telemetry.traces.tracers.%s', $tracingHttpKernel['tracer'])));
        } else {
            $defaultTracer = $config['traces']['default_tracer'] ?? array_key_first($config['traces']['tracers']);
            $trace->setArgument('$tracer', new Reference(sprintf('open_telemetry.traces.tracers.%s', $defaultTracer)));
        }
    }

    /** @phpstan-ignore-next-line */
    public function loadHttpKernelMeteringInstrumentation(array $config, ContainerBuilder $container): void
    {
        $httpKernelConfig = $config['instrumentation']['http_kernel'];
        $meteringHttpKernel = $httpKernelConfig['metering'];

        if (false === $meteringHttpKernel['enabled']) {
            return;
        }

        $metric = $container
            ->getDefinition('open_telemetry.instrumentation.http_kernel.metric.event_subscriber')
            ->addTag('kernel.event_subscriber');

        if (isset($meteringHttpKernel['meter'])) {
            $metric->setArgument('$meter', new Reference(sprintf('open_telemetry.metrics.meters.%s', $meteringHttpKernel['meter'])));
            if (!isset($config['metrics']['meters'][$meteringHttpKernel['meter']]['provider'])) {
                throw new \InvalidArgumentException('Meter provider has not found');
            }
            $meterProvider = $config['metrics']['meters'][$meteringHttpKernel['meter']]['provider'];
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

        $this->loadConsoleTracingInstrumentation($config, $container);
        $this->loadConsoleMeteringInstrumentation($config, $container);
    }

    /** @phpstan-ignore-next-line */
    public function loadConsoleTracingInstrumentation(array $config, ContainerBuilder $container): void
    {
        $consoleConfig = $config['instrumentation']['console'];
        $tracingConsoleConfig = $consoleConfig['tracing'];
        if (false === $consoleConfig['enabled']) {
            return;
        }

        $trace = $container
            ->getDefinition('open_telemetry.instrumentation.console.trace.event_subscriber')
            ->addTag('kernel.event_subscriber');

        if (isset($tracingConsoleConfig['tracer'])) {
            $trace->setArgument('$tracer', new Reference(sprintf('open_telemetry.traces.tracers.%s', $tracingConsoleConfig['tracer'])));
        } else {
            $defaultTracer = $config['traces']['default_tracer'] ?? array_key_first($config['traces']['tracers']);
            $trace->setArgument('$tracer', new Reference(sprintf('open_telemetry.traces.tracers.%s', $defaultTracer)));
        }
    }

    /** @phpstan-ignore-next-line */
    public function loadConsoleMeteringInstrumentation(array $config, ContainerBuilder $container): void
    {
        $consoleConfig = $config['instrumentation']['console'];
        $meteringConsoleConfig = $consoleConfig['metering'];
        if (false === $consoleConfig['enabled']) {
            return;
        }

        $metric = $container
            ->getDefinition('open_telemetry.instrumentation.console.metric.event_subscriber')
            ->addTag('kernel.event_subscriber');

        if (isset($meteringConsoleConfig['meter'])) {
            $metric->setArgument('$meter', new Reference(sprintf('open_telemetry.metrics.meters.%s', $config['meter'])));
            if (!isset($config['metrics']['meters'][$meteringConsoleConfig['meter']]['provider'])) {
                throw new \InvalidArgumentException('Meter provider has not found');
            }
            $meterProvider = $config['metrics']['meters'][$meteringConsoleConfig['meter']]['provider'];
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
     *      dsn: string,
     *      options?: ExporterOptions
     *  } $options
     */
    private function loadTraceExporter(string $name, array $options, ContainerBuilder $container): void
    {
        $exporterId = sprintf('open_telemetry.traces.exporters.%s', $name);
        $dsn = ExporterDsn::fromString($options['dsn']);
        $exporter = TraceExporterEnum::from($dsn->getExporter());

        $container
            ->setDefinition($exporterId, new ChildDefinition('open_telemetry.traces.exporter'))
            ->setClass($exporter->getClass())
            ->setFactory([$exporter->getFactoryClass(), 'createExporter'])
            ->setArguments([
                '$dsn' => $this->createExporterDsnDefinition($options['dsn'], $container),
                '$options' => $this->createExporterOptionsDefinition($options['options'] ?? [], $container),
            ]);
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
    private function loadTraceProvider(string $name, array $provider, ContainerBuilder $container): void
    {
        $providerId = sprintf('open_telemetry.traces.providers.%s', $name);
        $options = $this->getTraceProviderOptions($provider);

        $sampler = isset($provider['sampler']) ? $this->getTraceSamplerDefinition($provider['sampler'], $container) : $container->getDefinition('open_telemetry.traces.samplers.always_on');

        $container
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
     *     dsn: string,
     *     options?: ExporterOptions
     * } $options
     */
    private function loadMetricExporter(string $name, array $options, ContainerBuilder $container): void
    {
        $exporterId = sprintf('open_telemetry.metrics.exporters.%s', $name);
        $dsn = ExporterDsn::fromString($options['dsn']);
        $exporter = MetricExporterEnum::from($dsn->getExporter());

        $container
            ->setDefinition($exporterId, new ChildDefinition('open_telemetry.metrics.exporter'))
            ->setClass($exporter->getClass())
            ->setFactory([$exporter->getFactoryClass(), 'createExporter'])
            ->setArguments([
                '$dsn' => $this->createExporterDsnDefinition($options['dsn'], $container),
                '$options' => $this->createExporterOptionsDefinition(
                    $options['options'] ?? [],
                    $container,
                    'open_telemetry.metric_exporter_options',
                    self::METRIC_EXPORTER_OPTIONS
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
    private function loadMetricProvider(string $name, array $provider, ContainerBuilder $container): void
    {
        $providerId = sprintf('open_telemetry.metrics.providers.%s', $name);
        $options = $this->getMetricProviderOptions($provider);

        $container
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
     *      dsn: string,
     *      options?: ExporterOptions
     *  } $options
     */
    private function loadLogExporter(string $name, array $options, ContainerBuilder $container): void
    {
        $exporterId = sprintf('open_telemetry.logs.exporters.%s', $name);
        $dsn = ExporterDsn::fromString($options['dsn']);
        $exporter = LogExporterEnum::from($dsn->getExporter());

        $container
            ->setDefinition($exporterId, new ChildDefinition('open_telemetry.logs.exporter'))
            ->setClass($exporter->getClass())
            ->setFactory([$exporter->getFactoryClass(), 'createExporter'])
            ->setArguments([
                '$dsn' => $this->createExporterDsnDefinition($options['dsn'], $container),
                '$options' => $this->createExporterOptionsDefinition($options['options'] ?? [], $container),
            ]);
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
     *     factory: class-string<LogProcessorFactoryInterface>,
     *     class: class-string<LogRecordProcessorInterface>,
     *     processors: ?Reference[],
     *     exporter: ?Reference,
     * }
     */
    private function getLogProcessorOptions(array $processor): array
    {
        $processorEnum = LogProcessorEnum::from($processor['type']);
        $options = [
            'factory' => $processorEnum->getFactoryClass(),
            'class' => $processorEnum->getClass(),
            'processors' => [],
            'exporter' => null,
        ];

        // if (LogProcessorEnum::Batch === $processorEnum) {
        //     // TODO: Check batch options
        //     clock: OpenTelemetry\SDK\Common\Time\SystemClock
        //     max_queue_size: 2048
        //     schedule_delay: 5000
        //     export_timeout: 30000
        //     max_export_batch_size: 512
        //     auto_flush: true
        // }

        if (LogProcessorEnum::Multi === $processorEnum) {
            if (!isset($processor['processors']) || 0 === count($processor['processors'])) {
                throw new \InvalidArgumentException('Processors are not set or empty');
            }
            $options['processors'] = array_map(
                fn (string $processor) => new Reference(sprintf('open_telemetry.logs.processors.%s', $processor)),
                $processor['processors'],
            );
        }

        if (LogProcessorEnum::Simple === $processorEnum) {
            if (!isset($processor['exporter'])) {
                throw new \InvalidArgumentException('Exporter is not set');
            }
            $options['exporter'] = new Reference(sprintf('open_telemetry.logs.exporters.%s', $processor['exporter']));
        }

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
            ->setFactory([$options['factory'], 'createProvider'])
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
     *     factory: class-string<LoggerProviderFactoryInterface>,
     *     class: class-string<LoggerProviderInterface>,
     *     processor: ?Reference,
     * }
     */
    private function getLoggerProviderOptions(array $provider): array
    {
        $providerEnum = LoggerProviderEnum::from($provider['type']);
        $options = [
            'factory' => $providerEnum->getFactoryClass(),
            'class' => $providerEnum->getClass(),
            'processor' => null,
        ];

        if (LoggerProviderEnum::Default === $providerEnum) {
            if (!isset($provider['processor'])) {
                throw new \InvalidArgumentException('Processor is not set');
            }
            $options['processor'] = new Reference(sprintf('open_telemetry.logs.processors.%s', $provider['processor']));
        }

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

    private function createExporterDsnDefinition(string $dsn, ContainerBuilder $container): Definition
    {
        return $container
            ->getDefinition('open_telemetry.exporter_dsn')
            ->setArguments([$dsn]);
    }

    /**
     * @param array<string, mixed> $configuration
     * @param string[]             $extraOptions
     */
    private function createExporterOptionsDefinition(
        array $configuration,
        ContainerBuilder $container,
        string $definition = 'open_telemetry.exporter_options',
        array $extraOptions = [],
    ): Definition {
        return $container
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
