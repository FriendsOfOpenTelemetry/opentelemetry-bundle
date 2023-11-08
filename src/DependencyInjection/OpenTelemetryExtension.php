<?php

namespace GaelReyrol\OpenTelemetryBundle\DependencyInjection;

use GaelReyrol\OpenTelemetryBundle\Factory\ConsoleSpanExporterFactory;
use GaelReyrol\OpenTelemetryBundle\Factory\InMemorySpanExporterFactory;
use GaelReyrol\OpenTelemetryBundle\Factory\OtlpSpanExporterFactory;
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

        $this->loadHttpKernelInstrumentation($mergedConfig['instrumentation']['kernel'], $container);
        $this->loadConsoleInstrumentation($mergedConfig['instrumentation']['console'], $container);

        $this->loadTraces($mergedConfig['traces'], $container);
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
     * @param array{type: string, dsn: string} $exporter
     */
    private function getTraceExporterOptions(array $exporter): array
    {
        $options = $exporter;

        $options['factory'] = match (TraceExporterEnum::from($exporter['type'])) {
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
