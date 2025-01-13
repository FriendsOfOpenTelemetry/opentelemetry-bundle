<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class TracerLocatorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $tracers = $container->findTaggedServiceIds('open_telemetry.tracer');

        if (0 < count($tracers)) {
            if ($container->has('open_telemetry.instrumentation.console.trace.event_subscriber')) {
                $traceableConsoleEventSubscriber = $container->getDefinition('open_telemetry.instrumentation.console.trace.event_subscriber');
                $this->setTracerLocatorArgument($container, $traceableConsoleEventSubscriber, $tracers);
            }
            if ($container->has('open_telemetry.instrumentation.http_kernel.trace.event_subscriber')) {
                $traceableHttpKernelEventSubscriber = $container->getDefinition('open_telemetry.instrumentation.http_kernel.trace.event_subscriber');
                $this->setTracerLocatorArgument($container, $traceableHttpKernelEventSubscriber, $tracers);
            }
        }
    }

    /**
     * @param array<string, mixed> $tracers
     */
    private function setTracerLocatorArgument(ContainerBuilder $container, Definition $service, array $tracers): void
    {
        $service->setArgument(
            '$tracerLocator',
            ServiceLocatorTagPass::register($container, array_map(fn (string $id) => new Reference($id), array_keys($tracers))),
        );
    }
}
