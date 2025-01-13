<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class HttpKernelInstrumentationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (true === $container->hasParameter('open_telemetry.instrumentation.http_kernel.tracing.enabled')
            && true === $container->getParameter('open_telemetry.instrumentation.http_kernel.tracing.enabled')) {
            return;
        }

        $container->removeDefinition('open_telemetry.instrumentation.http_kernel.trace.event_subscriber');
        $container->removeDefinition('open_telemetry.instrumentation.http_kernel.trace.route_loader');
    }
}
