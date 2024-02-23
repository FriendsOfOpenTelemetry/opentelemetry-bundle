<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RemoveMessengerInstrumentationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->hasParameter('open_telemetry.instrumentation.messenger.tracing.enabled')
            || false === $container->getParameter('open_telemetry.instrumentation.messenger.tracing.enabled')) {
            $container->removeAlias('messenger.transport.open_telemetry_tracer.factory');
            $container->removeAlias('messenger.middleware.open_telemetry_tracer');

            $container->removeDefinition('open_telemetry.instrumentation.messenger.trace.event_subscriber');
            $container->removeDefinition('open_telemetry.instrumentation.messenger.trace.transport');
            $container->removeDefinition('open_telemetry.instrumentation.messenger.trace.transport_factory');
            $container->removeDefinition('open_telemetry.instrumentation.messenger.trace.middleware');
        }

        if (false === $container->hasParameter('open_telemetry.instrumentation.messenger.metering.enabled')
            || false === $container->getParameter('open_telemetry.instrumentation.messenger.metering.enabled')) {
        }
    }
}
