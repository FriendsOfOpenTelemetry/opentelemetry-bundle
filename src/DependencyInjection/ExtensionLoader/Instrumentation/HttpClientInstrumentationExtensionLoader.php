<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\ExtensionLoader\Instrumentation;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class HttpClientInstrumentationExtensionLoader extends AbstractInstrumentationExtensionLoader
{
    public function __construct()
    {
        parent::__construct('http_client');
    }

    protected function assertInstrumentationCanHappen(): void
    {
        //        if (!class_exists(HttpClientInterface::class)) {
        //            throw new \LogicException('To configure the Http Client instrumentation, you must first install the symfony/http-client package.');
        //        }
    }

    protected function setTracingDefinitions(): void
    {
        $decoratedService = $this->getDecoratedService();
        if (null === $decoratedService) {
            return;
        }

        $this->container->getDefinition('open_telemetry.instrumentation.http_client.trace.client')
            ->setArgument('$client', new Reference('open_telemetry.instrumentation.http_client.trace.client.inner'))
            ->setArgument('$tracer', $this->getInstrumentationTracerOrDefaultTracer())
            ->setDecoratedService($decoratedService[0], null, $decoratedService[1]);
    }

    protected function setMeteringDefinitions(): void
    {
        return;
    }

    /**
     * @return array{string, int}|null
     */
    private function getDecoratedService(): ?array
    {
        if ($this->container->hasDefinition('http_client.transport')) {
            return ['http_client.transport', -15];
        }

        if ($this->container->hasDefinition('http_client')) {
            return ['http_client', 15];
        }

        return null;
    }
}
