<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SetInstrumentationTypePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $consoleInstrumentationType = $container->getParameter('open_telemetry.instrumentation.console.type');
        if ($container->hasDefinition('open_telemetry.instrumentation.console.trace.event_subscriber')) {
        }

        $httpKernelInstrumentationType = $container->getParameter('open_telemetry.instrumentation.http_kernel.type');
        if ($container->hasDefinition('open_telemetry.instrumentation.http_kernel.trace.event_subscriber')) {
            $container->getDefinition('open_telemetry.instrumentation.http_kernel.trace.event_subscriber')
                ->addMethodCall('setInstrumentationType', [$httpKernelInstrumentationType]);
        }

        $messengerInstrumentationType = $container->getParameter('open_telemetry.instrumentation.messenger.type');
        if ($container->hasDefinition('open_telemetry.instrumentation.http_kernel.trace.event_subscriber')) {
        }
    }
}
