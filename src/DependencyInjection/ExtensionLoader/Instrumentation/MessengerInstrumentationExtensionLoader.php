<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\ExtensionLoader\Instrumentation;

use Symfony\Component\DependencyInjection\Reference;

final class MessengerInstrumentationExtensionLoader extends AbstractInstrumentationExtensionLoader
{
    public function __construct()
    {
        parent::__construct('messenger');
    }

    protected function assertInstrumentationCanHappen(): void
    {
    }

    protected function setTracingDefinitions(): void
    {
        $this->container
            ->getDefinition('open_telemetry.instrumentation.messenger.trace.event_subscriber')
            ->setArgument('$tracer', $this->getInstrumentationTracerOrDefaultTracer())
            ->addTag('kernel.event_subscriber');

        $this->container
            ->getDefinition('open_telemetry.instrumentation.messenger.trace.middleware')
            ->setArgument('$tracer', $this->getInstrumentationTracerOrDefaultTracer());
        $this->container->setAlias('messenger.middleware.open_telemetry_tracer', 'open_telemetry.instrumentation.messenger.trace.middleware');

        $this->container
            ->getDefinition('open_telemetry.instrumentation.messenger.trace.transport_factory')
            ->setArgument('$tracer', $this->getInstrumentationTracerOrDefaultTracer())
            ->setArgument('$transportFactory', new Reference('messenger.transport_factory'))
            ->addTag('messenger.transport_factory')
            ->addTag('kernel.reset', ['method' => 'reset']);
        $this->container->setAlias('messenger.transport.open_telemetry_tracer.factory', 'open_telemetry.instrumentation.messenger.trace.transport_factory');
    }

    protected function setMeteringDefinitions(): void
    {
    }
}
