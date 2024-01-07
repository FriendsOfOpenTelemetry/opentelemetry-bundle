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
    }

    protected function setMeteringDefinitions(): void
    {
    }
}
