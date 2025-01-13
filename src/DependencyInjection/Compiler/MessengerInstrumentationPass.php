<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;

class MessengerInstrumentationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->hasParameter('open_telemetry.instrumentation.messenger.tracing.enabled')
            || false === $container->getParameter('open_telemetry.instrumentation.messenger.tracing.enabled')) {
            return;
        }

        if (!interface_exists(MiddlewareInterface::class) || !interface_exists(TransportFactoryInterface::class)) {
            throw new \LogicException('Messenger instrumentation cannot be enabled because the symfony/messenger package is not installed.');
        }

        $container
            ->setAlias('messenger.transport.open_telemetry_tracer.factory', 'open_telemetry.instrumentation.messenger.trace.transport_factory');
        $container
            ->setAlias('messenger.middleware.open_telemetry_tracer', 'open_telemetry.instrumentation.messenger.trace.middleware');

        $container->getDefinition('open_telemetry.instrumentation.messenger.trace.transport_factory')
            ->addTag('messenger.transport_factory')
            ->addTag('kernel.reset', ['method' => 'reset']);
        $container->getDefinition('open_telemetry.instrumentation.messenger.trace.middleware')
            ->addTag('messenger.middleware');
    }
}
