<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TracerLocatorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $tracers = $container->findTaggedServiceIds('open_telemetry.tracer');

        if (0 !== count($tracers)) {
            if ($container->has('open_telemetry.instrumentation.console.trace.event_subscriber')) {
                $traceableConsoleEventSubscriber = $container->getDefinition('open_telemetry.instrumentation.console.trace.event_subscriber');
                $traceableConsoleEventSubscriber->setArgument(
                    '$tracerLocator',
                    ServiceLocatorTagPass::register($container, $tracers),
                );
            }
            if ($container->has('open_telemetry.instrumentation.http_kernel.trace.event_subscriber')) {
                $traceableHttpKernelEventSubscriber = $container->getDefinition('open_telemetry.instrumentation.http_kernel.trace.event_subscriber');
                $traceableHttpKernelEventSubscriber->setArgument(
                    '$tracerLocator',
                    ServiceLocatorTagPass::register($container, $tracers),
                );
            }
        }
    }
}
