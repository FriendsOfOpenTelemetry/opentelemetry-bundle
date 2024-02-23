<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RemoveDoctrineInstrumentationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->hasParameter('open_telemetry.instrumentation.doctrine.tracing.enabled')
            || false === $container->getParameter('open_telemetry.instrumentation.doctrine.tracing.enabled')) {
            $container->removeDefinition('open_telemetry.instrumentation.doctrine.trace.middleware');
            $container->removeDefinition('open_telemetry.instrumentation.doctrine.trace.event_subscriber');
        }

        if (false === $container->hasParameter('open_telemetry.instrumentation.doctrine.metering.enabled')
            || false === $container->getParameter('open_telemetry.instrumentation.doctrine.metering.enabled')) {
        }
    }
}
