<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SetConsoleTracingExcludeCommandsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->hasParameter('open_telemetry.instrumentation.console.tracing.exclude_commands')) {
            return;
        }

        if (false === $container->hasDefinition('open_telemetry.instrumentation.console.trace.event_subscriber')) {
            return;
        }

        $excludeCommands = $container->getParameter('open_telemetry.instrumentation.console.tracing.exclude_commands');
        $container->getDefinition('open_telemetry.instrumentation.console.trace.event_subscriber')
            ->addMethodCall('setExcludeCommands', [$excludeCommands]);
    }
}
