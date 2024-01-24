<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RemoveHttpKernelInstrumentationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->getParameter('open_telemetry.instrumentation.http_kernel.tracing.enabled')) {
            $container->removeDefinition('open_telemetry.instrumentation.http_kernel.trace.event_subscriber');
        }

        if (false === $container->getParameter('open_telemetry.instrumentation.http_kernel.metering.enabled')) {
        }
    }
}
