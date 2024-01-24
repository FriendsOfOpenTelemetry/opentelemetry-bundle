<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RemoveTwigInstrumentationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->getParameter('open_telemetry.instrumentation.twig.tracing.enabled')) {
            $container->removeDefinition('open_telemetry.instrumentation.twig.trace.extension');
        }

        if (false === $container->getParameter('open_telemetry.instrumentation.twig.metering.enabled')) {
        }
    }
}
