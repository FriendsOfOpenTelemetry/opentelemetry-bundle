<?php

namespace GaelReyrol\OpenTelemetryBundle\Tests\Unit\DependencyInjection;

use GaelReyrol\OpenTelemetryBundle\DependencyInjection\OpenTelemetryExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\EnvPlaceholderParameterBag;

class OpenTelemetryExtensionTest extends DependencyInjectionTest
{
    /**
     * @param array<string, array<string, mixed>> $config
     */
    protected function getContainer(array $config = []): ContainerBuilder
    {
        $container = new ContainerBuilder(new EnvPlaceholderParameterBag());

        $container->getCompilerPassConfig()->setOptimizationPasses([]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);

        $loader = new OpenTelemetryExtension();
        $loader->load($config, $container);
        $container->compile();

        return $container;
    }
}
