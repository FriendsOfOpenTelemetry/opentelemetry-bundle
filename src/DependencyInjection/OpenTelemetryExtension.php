<?php

namespace GaelReyrol\OpenTelemetryBundle\DependencyInjection;

use GaelReyrol\OpenTelemetryBundle\Factory\ConsoleSpanExporterFactory;
use GaelReyrol\OpenTelemetryBundle\Factory\InMemorySpanExporterFactory;
use GaelReyrol\OpenTelemetryBundle\Factory\OtlpSpanExporterFactory;
use GaelReyrol\OpenTelemetryBundle\Factory\SpanExporterFactoryInterface;
use GaelReyrol\OpenTelemetryBundle\Factory\ZipkinSpanExporterFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\HttpKernel\HttpKernel;

/**
 * @phpstan-type ComponentInstrumentationOptions array{enabled: bool, provider: string}
 */
final class OpenTelemetryExtension extends ConfigurableExtension
{
    private string $defaultTraceProvider;

    /** @phpstan-ignore-next-line */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(dirname(__DIR__).'/Resources/config'));
        $loader->load('services.php');

        $this->loadService($mergedConfig['service'], $container);

        $this->loadHttpKernelInstrumentation($mergedConfig['instrumentation']['kernel'], $container);
        $this->loadConsoleInstrumentation($mergedConfig['instrumentation']['console'], $container);

        $this->loadTraces($mergedConfig['traces'], $container);
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

        $container->getDefinition('open_telemetry.instrumentation.http_kernel.event_subscriber')->addTag('kernel.event_subscriber');
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

        $container->getDefinition('open_telemetry.instrumentation.console.event_subscriber')->addTag('kernel.event_subscriber');
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

        $this->defaultTraceProvider = $config['default_provider'];
        foreach ($config['providers'] as $name => $provider) {
            $this->loadTraceProvider($name, $provider, $container);
        }
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
        $configuration = $container->setDefinition(sprintf('open_telemetry.traces.exporters.%s.configuration', $name), new ChildDefinition('open_telemetry.traces.exporter.configuration'));

        $exporterId = sprintf('open_telemetry.traces.exporters.%s', $name);

        $options = $this->getTraceExporterOptions($exporter);

        $container
            ->setDefinition($exporterId, new ChildDefinition('open_telemetry.traces.exporter'))
            ->setPublic(true)
            ->setArguments([
                $options,
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

    private function loadTraceProcessor(string $name, array $processor, ContainerBuilder $container): void
    {
    }

    private function loadTraceProvider(string $name, array $provider, ContainerBuilder $container): void
    {
    }
}
