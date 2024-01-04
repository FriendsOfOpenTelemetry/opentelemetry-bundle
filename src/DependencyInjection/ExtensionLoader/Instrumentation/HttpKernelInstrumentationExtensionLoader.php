<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\ExtensionLoader\Instrumentation;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\HttpKernel;

final class HttpKernelInstrumentationExtensionLoader extends AbstractInstrumentationExtensionLoader
{
    public function __construct()
    {
        parent::__construct('http_kernel');
    }

    protected function assertInstrumentationCanHappen(): void
    {
        if (!class_exists(HttpKernel::class)) {
            throw new \LogicException('To configure the HttpKernel instrumentation, you must first install the symfony/http-kernel package.');
        }
    }

    protected function setTracingDefinitions(): void
    {
        $tracingConfigSection = $this->getTracingConfigInstrumentationSection();

        $trace = $this->container
            ->getDefinition('open_telemetry.instrumentation.http_kernel.trace.event_subscriber')
            ->setArgument('$requestHeaders', $tracingConfigSection['request_headers'])
            ->setArgument('$responseHeaders', $tracingConfigSection['response_headers'])
            ->addTag('kernel.event_subscriber');

        if (isset($tracingConfigSection['tracer'])) {
            $trace->setArgument('$tracer', new Reference(sprintf('open_telemetry.traces.tracers.%s', $tracingConfigSection['tracer'])));
        } else {
            $defaultTracer = $this->config['traces']['default_tracer'] ?? array_key_first($this->config['traces']['tracers']);
            $trace->setArgument('$tracer', new Reference(sprintf('open_telemetry.traces.tracers.%s', $defaultTracer)));
        }
    }

    protected function setMeteringDefinitions(): void
    {
        $meteringConfigSection = $this->getMeteringConfigInstrumentationSection();

        $metric = $this->container
            ->getDefinition('open_telemetry.instrumentation.http_kernel.metric.event_subscriber')
            ->addTag('kernel.event_subscriber');

        if (isset($meteringConfigSection['meter'])) {
            $metric->setArgument('$meter', new Reference(sprintf('open_telemetry.metrics.meters.%s', $meteringConfigSection['meter'])));
            if (!isset($this->config['metrics']['meters'][$meteringConfigSection['meter']]['provider'])) {
                throw new \InvalidArgumentException('Meter provider has not found');
            }
            $meterProvider = $this->config['metrics']['meters'][$meteringConfigSection['meter']]['provider'];
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
