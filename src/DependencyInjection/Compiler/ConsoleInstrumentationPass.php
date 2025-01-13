<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ConsoleInstrumentationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (true === $container->hasParameter('open_telemetry.instrumentation.console.tracing.enabled')
            && true === $container->getParameter('open_telemetry.instrumentation.console.tracing.enabled')) {
            return;
        }

        $container->removeDefinition('open_telemetry.instrumentation.console.trace.event_subscriber');
    }
}
