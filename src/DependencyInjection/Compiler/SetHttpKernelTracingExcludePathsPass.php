<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SetHttpKernelTracingExcludePathsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->hasParameter('open_telemetry.instrumentation.http_kernel.tracing.exclude_paths')) {
            return;
        }

        if (false === $container->hasDefinition('open_telemetry.instrumentation.http_kernel.trace.event_subscriber')) {
            return;
        }

        $excludePaths = $container->getParameter('open_telemetry.instrumentation.http_kernel.tracing.exclude_paths');
        $container->getDefinition('open_telemetry.instrumentation.http_kernel.trace.event_subscriber')
            ->addMethodCall('setExcludePaths', [$excludePaths]);
    }
}
