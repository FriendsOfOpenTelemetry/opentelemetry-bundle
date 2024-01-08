<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\ExtensionLoader\Instrumentation;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\ExtensionLoader\ExtensionLoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

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

        var_dump($this->key);
        if (false === $this->isConfigInstrumentationSectionEnabled()) {
            var_dump('not enabled');

            return;
        }

        $this->assertInstrumentationCanHappen();

        $this->loadTracingInstrumentation();
        $this->loadMeteringInstrumentation();
    }

    private function isConfigInstrumentationSectionEnabled(): bool
    {
        return true === $this->getConfigInstrumentationSection()['enabled'];
    }

    protected function getConfigInstrumentationSection(): array
    {
        return $this->config['instrumentation'][$this->key];
    }

    private function isTracingConfigInstrumentationSectionEnabled(): bool
    {
        return true === $this->getTracingConfigInstrumentationSection()['enabled'];
    }

    protected function getTracingConfigInstrumentationSection(): array
    {
        return $this->getConfigInstrumentationSection()['tracing'];
    }

    protected function getInstrumentationTracerOrDefaultTracer(): Reference
    {
        $tracingConfigInstrumentationSection = $this->getTracingConfigInstrumentationSection();

        if (isset($tracingConfigInstrumentationSection['tracer'])) {
            return new Reference(sprintf('open_telemetry.traces.tracers.%s', $tracingConfigInstrumentationSection['tracer']));
        }

        $defaultTracer = $this->config['traces']['default_tracer'] ?? array_key_first($this->config['traces']['tracers']);

        return new Reference(sprintf('open_telemetry.traces.tracers.%s', $defaultTracer));
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
        return true === $this->getMeteringConfigInstrumentationSection()['enabled'];
    }

    protected function getMeteringConfigInstrumentationSection(): array
    {
        return $this->getConfigInstrumentationSection()['metering'];
    }

    protected function getInstrumentationMeterOrDefaultMeter(): Reference
    {
        $meteringConfigInstrumentationSection = $this->getConfigInstrumentationSection();

        if (isset($meteringConfigInstrumentationSection['meter'])) {
            return new Reference(sprintf('open_telemetry.metrics.meters.%s', $this->config['meter']));
        }

        $defaultMeter = $this->config['metrics']['default_meter'] ?? array_key_first($this->config['metrics']['meters']);

        return new Reference(sprintf('open_telemetry.metrics.meters.%s', $defaultMeter));
    }

    protected function getInstrumentationMeterProviderOrDefaultMeterProvider(): Reference
    {
        $meteringConfigInstrumentationSection = $this->getConfigInstrumentationSection();

        if (isset($meteringConfigInstrumentationSection['meter'])) {
            if (!isset($this->config['metrics']['meters'][$meteringConfigInstrumentationSection['meter']]['provider'])) {
                throw new \InvalidArgumentException('Meter provider has not found');
            }
            $meterProvider = $this->config['metrics']['meters'][$meteringConfigInstrumentationSection['meter']]['provider'];

            return new Reference(sprintf('open_telemetry.metrics.providers.%s', $meterProvider));
        }

        $defaultMeter = $this->config['metrics']['default_meter'] ?? array_key_first($this->config['metrics']['meters']);
        if (!isset($this->config['metrics']['meters'][$defaultMeter]['provider'])) {
            throw new \InvalidArgumentException('Meter provider has not found');
        }
        $meterProvider = $this->config['metrics']['meters'][$defaultMeter]['provider'];

        return new Reference(sprintf('open_telemetry.metrics.providers.%s', $meterProvider));
    }

    private function loadMeteringInstrumentation(): void
    {
        if (false === $this->isMeteringConfigInstrumentationSectionEnabled()) {
            return;
        }

        $this->setMeteringDefinitions();
    }
}
