<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\ExtensionLoader\Instrumentation;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class HttpClientInstrumentationExtensionLoader extends AbstractInstrumentationExtensionLoader
{
    public function __construct()
    {
        parent::__construct('http_client');
    }

    protected function assertInstrumentationCanHappen(): void
    {
        if (!class_exists(HttpClientInterface::class)) {
            throw new \LogicException('To configure the Http Client instrumentation, you must first install the symfony/http-client package.');
        }
    }

    protected function setTracingDefinitions(): void
    {
        $this->container
            ->getDefinition('open_telemetry.instrumentation.twig.trace.extension')
            ->addTag('twig.extension')
            ->setArgument('$tracer', $this->getInstrumentationTracerOrDefaultTracer());
    }

    protected function setMeteringDefinitions(): void
    {
        return;
    }
}
