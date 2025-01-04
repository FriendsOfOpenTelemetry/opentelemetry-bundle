<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

final class OpenTelemetryExtension extends ConfigurableExtension
{
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(dirname(__DIR__).'/Resources/config'));
        $loader->load('services.php');
        $loader->load('services_transports.php');
        $loader->load('services_logs.php');
        $loader->load('services_metrics.php');
        $loader->load('services_traces.php');

        $this->loadServiceParams($mergedConfig['service'], $container);

        (new OpenTelemetryInstrumentationExtension())($mergedConfig['instrumentation'], $container, $loader);
        (new OpenTelemetryTracesExtension())($mergedConfig['traces'], $container);
        (new OpenTelemetryMetricsExtension())($mergedConfig['metrics'], $container);
        (new OpenTelemetryLogsExtension())($mergedConfig['logs'], $container);
    }

    /**
     * @param array{
     *     namespace: string,
     *     name: string,
     *     version: string,
     *     environment: string
     * } $config
     */
    private function loadServiceParams(array $config, ContainerBuilder $container): void
    {
        $container->setParameter('open_telemetry.service.namespace', $config['namespace']);
        $container->setParameter('open_telemetry.service.name', $config['name']);
        $container->setParameter('open_telemetry.service.version', $config['version']);
        $container->setParameter('open_telemetry.service.environment', $config['environment']);

        $container->getDefinition('open_telemetry.resource_info')
            ->setArguments([
                $config['namespace'],
                $config['name'],
                $config['version'],
                $config['environment'],
            ]);
    }
}
