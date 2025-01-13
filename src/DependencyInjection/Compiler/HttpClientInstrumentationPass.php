<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpClient\HttpClient;

class HttpClientInstrumentationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->hasParameter('open_telemetry.instrumentation.http_client.tracing.enabled')
            || false === $container->getParameter('open_telemetry.instrumentation.http_client.tracing.enabled')) {
            $container->removeDefinition('open_telemetry.instrumentation.http_client.trace.client');

            return;
        }

        if (!class_exists(HttpClient::class)) {
            throw new \LogicException('Http client instrumentation cannot be enabled because the symfony/http-client package is not installed.');
        }

        $decoratedService = $this->getDecoratedService($container);
        if (null === $decoratedService) {
            $container->removeDefinition('open_telemetry.instrumentation.http_client.trace.client');

            return;
        }

        $container->getDefinition('open_telemetry.instrumentation.http_client.trace.client')
            ->setArgument('$client', new Reference('.inner'))
            ->setDecoratedService($decoratedService[0], null, $decoratedService[1]);
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
