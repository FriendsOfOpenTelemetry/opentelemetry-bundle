<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RemoveConsoleInstrumentationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->getParameter('open_telemetry.instrumentation.console.tracing.enabled')) {
            $container->removeDefinition('open_telemetry.instrumentation.console.trace.event_subscriber');
        }

        if (false === $container->getParameter('open_telemetry.instrumentation.console.metering.enabled')) {
        }
    }
}
