<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\ExtensionLoader\Instrumentation;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\ExtensionLoader\ExtensionLoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class AbstractInstrumentationExtensionLoader implements ExtensionLoaderInterface
{
    protected array $config;
    protected ContainerBuilder $container;

    public function __construct(
        protected readonly string $key,
    ) {
        $this->config = [];
        $this->container = new ContainerBuilder();
    }

    abstract protected function assertInstrumentationCanHappen(): void;

    abstract protected function setTracingDefinitions(): void;

    abstract protected function setMeteringDefinitions(): void;

    public function load(array $config, ContainerBuilder $container): void
    {
        $this->config = $config;
        $this->container = $container;

        if (false === $this->isConfigInstrumentationSectionEnabled()) {
            return;
        }

        $this->assertInstrumentationCanHappen();

        $this->loadTracingInstrumentation();
        $this->loadMeteringInstrumentation();
    }

    private function isConfigInstrumentationSectionEnabled(): bool
    {
        return $this->getConfigInstrumentationSection()['enabled'];
    }

    protected function getConfigInstrumentationSection(): array
    {
        return $this->config['instrumentation'][$this->key];
    }

    private function isTracingConfigInstrumentationSectionEnabled(): bool
    {
        return $this->getTracingConfigInstrumentationSection()['enabled'];
    }

    protected function getTracingConfigInstrumentationSection(): array
    {
        return $this->getConfigInstrumentationSection()['tracing'];
    }

    private function loadTracingInstrumentation(): void
    {
        if (false === $this->isTracingConfigInstrumentationSectionEnabled()) {
            return;
        }

        $this->setTracingDefinitions();
    }

    private function isMeteringConfigInstrumentationSectionEnabled(): bool
    {
        return $this->getMeteringConfigInstrumentationSection()['enabled'];
    }

    protected function getMeteringConfigInstrumentationSection(): array
    {
        return $this->getConfigInstrumentationSection()['metering'];
    }

    private function loadMeteringInstrumentation(): void
    {
        if (false === $this->isMeteringConfigInstrumentationSectionEnabled()) {
            return;
        }

        $this->setMeteringDefinitions();
    }
}
