<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ConsoleInstrumentationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->hasParameter('open_telemetry.instrumentation.console.tracing.enabled')
            || false === $container->getParameter('open_telemetry.instrumentation.console.tracing.enabled')) {
            $container->removeDefinition('open_telemetry.instrumentation.console.trace.event_subscriber');

            return;
        }

        $container
            ->getDefinition('open_telemetry.instrumentation.console.trace.event_subscriber')
            ->addTag('kernel.event_subscriber');
    }
}
