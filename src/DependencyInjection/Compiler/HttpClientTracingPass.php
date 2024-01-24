<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class HttpClientTracingPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->getParameter('open_telemetry.instrumentation.http_client.enabled')) {
            return;
        }

        $decoratedService = $this->getDecoratedService($container);
        if (null === $decoratedService) {
            return;
        }

        if (true === $container->getParameter('open_telemetry.instrumentation.http_client.tracing.enabled')) {
            $container->getDefinition('open_telemetry.instrumentation.http_client.trace.client')
                ->setArgument('$client', new Reference('.inner'))
                ->setDecoratedService($decoratedService[0], null, $decoratedService[1]);
        }

        if (true === $container->getParameter('open_telemetry.instrumentation.http_client.metering.enabled')) {
        }
    }

    /**
     * @return array{string, int}|null
     */
    private function getDecoratedService(ContainerBuilder $container): ?array
    {
        if ($container->hasDefinition('http_client.transport')) {
            return ['http_client.transport', -15];
        }

        if ($container->hasDefinition('http_client')) {
            return ['http_client', 15];
        }

        return null;
    }
}
