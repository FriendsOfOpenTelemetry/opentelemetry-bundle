<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\ExtensionLoader\Instrumentation;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;

final class DoctrineInstrumentationExtensionLoader extends AbstractInstrumentationExtensionLoader
{
    public function __construct()
    {
        parent::__construct('doctrine');
    }

    protected function assertInstrumentationCanHappen(): void
    {
        if (!class_exists(DoctrineBundle::class)) {
            throw new \LogicException('To configure the Doctrine instrumentation, you must first install the doctrine/doctrine-bundle package.');
        }
    }

    protected function setTracingDefinitions(): void
    {
        $this->container
            ->getDefinition('open_telemetry.instrumentation.doctrine.trace.middleware')
            ->addTag('doctrine.middleware')
            ->setArgument('$tracer', $this->getInstrumentationTracerOrDefaultTracer());
    }

    protected function setMeteringDefinitions(): void
    {
        return;
    }
}
