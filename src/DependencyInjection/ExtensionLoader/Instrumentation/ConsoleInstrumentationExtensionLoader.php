<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\ExtensionLoader\Instrumentation;

use Symfony\Component\Console\Application;

final class ConsoleInstrumentationExtensionLoader extends AbstractInstrumentationExtensionLoader
{
    public function __construct()
    {
        parent::__construct('console');
    }

    protected function assertInstrumentationCanHappen(): void
    {
        if (!class_exists(Application::class)) {
            throw new \LogicException('To configure the Console instrumentation, you must first install the symfony/console package.');
        }
    }

    protected function setTracingDefinitions(): void
    {
        $this->container
            ->getDefinition('open_telemetry.instrumentation.console.trace.event_subscriber')
            ->addTag('kernel.event_subscriber')
            ->setArgument('$tracer', $this->getInstrumentationTracerOrDefaultTracer());
    }

    protected function setMeteringDefinitions(): void
    {
        $this->container
            ->getDefinition('open_telemetry.instrumentation.console.metric.event_subscriber')
            ->addTag('kernel.event_subscriber')
            ->setArgument('$meter', $this->getInstrumentationMeterOrDefaultMeter())
            ->setArgument('$meterProvider', $this->getInstrumentationMeterProviderOrDefaultMeterProvider());
    }
}
