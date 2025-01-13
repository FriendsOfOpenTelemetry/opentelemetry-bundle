<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DoctrineInstrumentationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->hasParameter('open_telemetry.instrumentation.doctrine.tracing.enabled')
            || false === $container->getParameter('open_telemetry.instrumentation.doctrine.tracing.enabled')) {
            return;
        }

        $container->getDefinition('open_telemetry.instrumentation.doctrine.trace.middleware')
            ->addTag('doctrine.middleware');
    }
}
