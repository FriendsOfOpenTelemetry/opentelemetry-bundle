<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\ExtensionLoader\Instrumentation;

use Symfony\Component\DependencyInjection\Reference;

final class MailerInstrumentationExtensionLoader extends AbstractInstrumentationExtensionLoader
{
    public function __construct()
    {
        parent::__construct('mailer');
    }

    protected function assertInstrumentationCanHappen(): void
    {
    }

    protected function setTracingDefinitions(): void
    {
        $this->container
            ->getDefinition('open_telemetry.instrumentation.mailer.trace.event_subscriber')
            ->setArgument('$tracer', $this->getInstrumentationTracerOrDefaultTracer())
            ->addTag('kernel.event_subscriber');

        $this->container->getDefinition('open_telemetry.instrumentation.mailer.trace.mailer')
            ->setArgument('$tracer', $this->getInstrumentationTracerOrDefaultTracer())
            ->setArgument('$mailer', new Reference('.inner'))
            ->setDecoratedService('mailer.mailer');

        $this->container
            ->getDefinition('open_telemetry.instrumentation.mailer.trace.transports')
            ->setArgument('$tracer', $this->getInstrumentationTracerOrDefaultTracer());

        $this->container
            ->getDefinition('open_telemetry.instrumentation.mailer.trace.default_transport')
            ->setArgument('$tracer', $this->getInstrumentationTracerOrDefaultTracer());
    }

    protected function setMeteringDefinitions(): void
    {
    }
}
