<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\InstrumentationTypeEnum;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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
final class OpenTelemetryInstrumentationExtension
{
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
    public function __invoke(array $config, ContainerBuilder $container, LoaderInterface $loader): void
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

            if ($instrumentation['tracing']['enabled']) {
                $loader->load('instrumentation/'.$name.'.php');
            }
        }
    }
}
