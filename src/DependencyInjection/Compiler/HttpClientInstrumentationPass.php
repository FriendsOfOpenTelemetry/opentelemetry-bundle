<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class HttpClientInstrumentationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $decoratedService = $this->getDecoratedService($container);
        if (null === $decoratedService) {
            $container->removeDefinition('open_telemetry.instrumentation.http_client.trace.client');

            return;
        }

        if (true === $container->hasParameter('open_telemetry.instrumentation.http_client.tracing.enabled')
            && true === $container->getParameter('open_telemetry.instrumentation.http_client.tracing.enabled')) {
            $container->getDefinition('open_telemetry.instrumentation.http_client.trace.client')
                ->setArgument('$client', new Reference('.inner'))
                ->setDecoratedService($decoratedService[0], null, $decoratedService[1]);
        } else {
            $container->removeDefinition('open_telemetry.instrumentation.http_client.trace.client');
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
