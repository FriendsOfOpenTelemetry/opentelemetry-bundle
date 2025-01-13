<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler;

use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TwigInstrumentationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->hasParameter('open_telemetry.instrumentation.twig.tracing.enabled')
            || false === $container->getParameter('open_telemetry.instrumentation.twig.tracing.enabled')) {
            return;
        }

        if (!class_exists(TwigBundle::class)) {
            throw new \LogicException('Twig instrumentation cannot be enabled because the symfony/twig-bundle package is not installed.');
        }

        $container->getDefinition('open_telemetry.instrumentation.twig.trace.extension')
            ->addTag('twig.extension');
    }
}
