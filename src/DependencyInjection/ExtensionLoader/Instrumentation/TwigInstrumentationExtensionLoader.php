<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\ExtensionLoader\Instrumentation;

use Symfony\Bundle\TwigBundle\TwigBundle;

final class TwigInstrumentationExtensionLoader extends AbstractInstrumentationExtensionLoader
{
    public function __construct()
    {
        parent::__construct('twig');
    }

    protected function assertInstrumentationCanHappen(): void
    {
        if (!class_exists(TwigBundle::class)) {
            throw new \LogicException('To configure the Twig instrumentation, you must first install the symfony/twig-bundle package.');
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
