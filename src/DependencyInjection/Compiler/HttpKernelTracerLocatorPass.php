<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class HttpKernelTracerLocatorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->has('open_telemetry.instrumentation.http_kernel.trace.event_subscriber')) {
            $tracers = $container->findTaggedServiceIds('open_telemetry.tracer');

            if (0 !== count($tracers)) {
                $traceableHttpKernelEventSubscriber = $container->getDefinition('open_telemetry.instrumentation.http_kernel.trace.event_subscriber');
                $traceableHttpKernelEventSubscriber->setArgument(
                    '$tracerLocator',
                    ServiceLocatorTagPass::register($container, $tracers),
                );
            }
        }
    }
}
