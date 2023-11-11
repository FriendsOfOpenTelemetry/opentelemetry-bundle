<?php

namespace GaelReyrol\OpenTelemetryBundle\Tests\Unit\DependencyInjection;

use GaelReyrol\OpenTelemetryBundle\DependencyInjection\OpenTelemetryExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class FixtureOpenTelemetryExtensionTest extends DependencyInjectionTest
{
    public function testMinimal(): void
    {
        $container = $this->getContainer('minimal');

        self::assertTrue($container->hasDefinition('open_telemetry.traces.exporters.json'));
        self::assertTrue($container->hasDefinition('open_telemetry.traces.processors.simple'));
        self::assertTrue($container->hasDefinition('open_telemetry.traces.providers.main'));
        self::assertTrue($container->hasDefinition('open_telemetry.traces.tracers.main'));
    }

    protected function getContainer(string $fixture): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->registerExtension(new OpenTelemetryExtension());

        $this->loadFixture($container, $fixture);

        $container->getCompilerPassConfig()->setOptimizationPasses([]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);
        $container->compile();

        return $container;
    }

    abstract protected function loadFixture(ContainerBuilder $container, string $fixture): void;
}
