<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\ExtensionLoader\Instrumentation;

use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\Reference;

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
        $tracingConsoleConfig = $this->getTracingConfigInstrumentationSection();

        $trace = $this->container
            ->getDefinition('open_telemetry.instrumentation.console.trace.event_subscriber')
            ->addTag('kernel.event_subscriber');

        if (isset($tracingConsoleConfig['tracer'])) {
            $trace->setArgument('$tracer', new Reference(sprintf('open_telemetry.traces.tracers.%s', $tracingConsoleConfig['tracer'])));
        } else {
            $defaultTracer = $this->config['traces']['default_tracer'] ?? array_key_first($this->config['traces']['tracers']);
            $trace->setArgument('$tracer', new Reference(sprintf('open_telemetry.traces.tracers.%s', $defaultTracer)));
        }
    }

    protected function setMeteringDefinitions(): void
    {
        $meteringConsoleConfig = $this->getMeteringConfigInstrumentationSection();

        $metric = $this->container
            ->getDefinition('open_telemetry.instrumentation.console.metric.event_subscriber')
            ->addTag('kernel.event_subscriber');

        if (isset($meteringConsoleConfig['meter'])) {
            $metric->setArgument('$meter', new Reference(sprintf('open_telemetry.metrics.meters.%s', $this->config['meter'])));
            if (!isset($this->config['metrics']['meters'][$meteringConsoleConfig['meter']]['provider'])) {
                throw new \InvalidArgumentException('Meter provider has not found');
            }
            $meterProvider = $this->config['metrics']['meters'][$meteringConsoleConfig['meter']]['provider'];
            $metric->setArgument('$meterProvider', new Reference(sprintf('open_telemetry.metrics.providers.%s', $meterProvider)));
        } else {
            $defaultMeter = $this->config['metrics']['default_meter'] ?? array_key_first($this->config['metrics']['meters']);
            $metric->setArgument('$meter', new Reference(sprintf('open_telemetry.metrics.meters.%s', $defaultMeter)));
            if (!isset($this->config['metrics']['meters'][$defaultMeter]['provider'])) {
                throw new \InvalidArgumentException('Meter provider has not found');
            }
            $meterProvider = $this->config['metrics']['meters'][$defaultMeter]['provider'];
            $metric->setArgument('$meterProvider', new Reference(sprintf('open_telemetry.metrics.providers.%s', $meterProvider)));
        }
    }
}
