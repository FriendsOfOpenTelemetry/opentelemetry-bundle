<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection;

use Monolog\Level;
use OpenTelemetry\Contrib\Logs\Monolog\Handler;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

/**
 * @phpstan-type InstrumentationConfig array{
 *     enabled: bool,
 *     tracing: TracingInstrumentationConfig,
 *     metering: MeteringInstrumentationConfig,
 * }
 * @phpstan-type TracingInstrumentationConfig array{
 *     enabled: bool,
 *     tracer: string,
 *     request_headers: array<string, mixed>,
 *     response_headers: array<string, mixed>,
 * }
 * @phpstan-type MeteringInstrumentationConfig array{
 *     enabled: bool,
 *     meter: string,
 * }
 */
final class OpenTelemetryExtension extends ConfigurableExtension
{
    /** @phpstan-ignore-next-line */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(dirname(__DIR__).'/Resources/config'));
        $loader->load('services.php');
        $loader->load('services_tracing_instrumentation.php');

        $this->loadService($mergedConfig['service'], $container);
        $this->loadInstrumentationParams($mergedConfig['instrumentation'], $container);

        (new OpenTelemetryTracesExtension())($mergedConfig['traces'], $container);
        (new OpenTelemetryMetricsExtension())($mergedConfig['metrics'], $container);
        (new OpenTelemetryLogsExtension())($mergedConfig['logs'], $container);

        $this->loadMonologHandlers($mergedConfig['logs'], $container);
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
     * @param array{
     *     cache: InstrumentationConfig,
     *     console: InstrumentationConfig,
     *     doctrine: InstrumentationConfig,
     *     http_client: InstrumentationConfig,
     *     http_kernel: InstrumentationConfig,
     *     mailer: InstrumentationConfig,
     *     messenger: InstrumentationConfig,
     *     twig: InstrumentationConfig,
     * } $config
     */
    private function loadInstrumentationParams(array $config, ContainerBuilder $container): void
    {
        foreach ($config as $name => $instrumentation) {
            $container->setParameter(
                sprintf('open_telemetry.instrumentation.%s.tracing.enabled', $name),
                $instrumentation['tracing']['enabled'],
            );
            $container->setParameter(
                sprintf('open_telemetry.instrumentation.%s.tracing.request_headers', $name),
                $instrumentation['tracing']['request_headers'],
            );
            $container->setParameter(
                sprintf('open_telemetry.instrumentation.%s.tracing.response_headers', $name),
                $instrumentation['tracing']['response_headers'],
            );
            $container->setParameter(
                sprintf('open_telemetry.instrumentation.%s.metering.enabled', $name),
                $instrumentation['metering']['enabled'],
            );
        }
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

            //            $container->setParameter('monolog.handlers.open_telemetry', [
            //                'type' => 'service',
            //                'id' => $handlerId,
            //            ]);
        }
    }
}
