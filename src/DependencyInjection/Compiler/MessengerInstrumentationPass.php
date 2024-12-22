<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MessengerInstrumentationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $container
            ->getDefinition('open_telemetry.instrumentation.messenger.trace.transport_factory')
            ->addTag('messenger.transport_factory')
            ->addTag('kernel.reset', ['method' => 'reset']);

        $container->setAlias('messenger.transport.open_telemetry_tracer.factory', 'open_telemetry.instrumentation.messenger.trace.transport_factory');
        $container->setAlias('messenger.middleware.open_telemetry_tracer', 'open_telemetry.instrumentation.messenger.trace.middleware');

        if (false === $container->hasParameter('open_telemetry.instrumentation.messenger.tracing.enabled')
            || false === $container->getParameter('open_telemetry.instrumentation.messenger.tracing.enabled')) {
            $container->removeAlias('messenger.transport.open_telemetry_tracer.factory');
            $container->removeAlias('messenger.middleware.open_telemetry_tracer');

            $container->removeDefinition('open_telemetry.instrumentation.messenger.trace.transport');
            $container->removeDefinition('open_telemetry.instrumentation.messenger.trace.transport_factory');
            $container->removeDefinition('open_telemetry.instrumentation.messenger.trace.middleware');
        }
    }
}
