<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\InstrumentationTypeEnum;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

/**
 * @phpstan-type InstrumentationConfig array{
 *     type?: string,
 *     tracing: TracingInstrumentationConfig,
 *     metering: MeteringInstrumentationConfig,
 * }
 * @phpstan-type TracingInstrumentationConfig array{
 *     enabled: bool,
 *     tracer: ?string,
 * }
 * @phpstan-type MeteringInstrumentationConfig array{
 *     enabled: bool,
 *     meter: ?string,
 * }
 */
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
        $loader->load('services_tracing_instrumentation.php');

        $this->loadServiceParams($mergedConfig['service'], $container);
        $this->loadInstrumentationParams($mergedConfig['instrumentation'], $container);

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
        // TODO These values are not passed to the OpenTelemetry SDK
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
            if (isset($instrumentation['type'])) {
                $container->setParameter(
                    sprintf('open_telemetry.instrumentation.%s.type', $name),
                    InstrumentationTypeEnum::from($instrumentation['type']),
                );
            }
            $container->setParameter(
                sprintf('open_telemetry.instrumentation.%s.tracing.tracer', $name),
                $instrumentation['tracing']['tracer'] ?? 'default_tracer',
            );
            $container->setParameter(
                sprintf('open_telemetry.instrumentation.%s.metering.enabled', $name),
                $instrumentation['metering']['enabled'],
            );
            $container->setParameter(
                sprintf('open_telemetry.instrumentation.%s.metering.meter', $name),
                $instrumentation['metering']['meter'] ?? 'default_meter',
            );
        }
    }
}
