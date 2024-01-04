<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\ExtensionLoader\Instrumentation\ConsoleInstrumentationExtensionLoader;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\ExtensionLoader\Instrumentation\HttpKernelInstrumentationExtensionLoader;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\ExtensionLoader\LogsExtensionLoader;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\ExtensionLoader\MetricsExtensionLoader;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\ExtensionLoader\TracesExtensionLoader;
use Monolog\Level;
use OpenTelemetry\Contrib\Logs\Monolog\Handler;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

final class OpenTelemetryExtension extends ConfigurableExtension
{
    /** @phpstan-ignore-next-line */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(dirname(__DIR__).'/Resources/config'));
        $loader->load('services.php');

        $this->loadService($mergedConfig['service'], $container);

        (new TracesExtensionLoader())->load($mergedConfig['traces'], $container);
        (new MetricsExtensionLoader())->load($mergedConfig['metrics'], $container);
        (new LogsExtensionLoader())->load($mergedConfig['logs'], $container);

        $this->loadMonologHandlers($mergedConfig['logs'], $container);

        (new HttpKernelInstrumentationExtensionLoader())->load($mergedConfig, $container);
        (new ConsoleInstrumentationExtensionLoader())->load($mergedConfig, $container);
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
