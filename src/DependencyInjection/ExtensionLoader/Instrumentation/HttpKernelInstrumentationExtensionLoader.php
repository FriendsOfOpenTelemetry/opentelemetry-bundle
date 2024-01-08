<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\ExtensionLoader\Instrumentation;

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

        $this->container
            ->getDefinition('open_telemetry.instrumentation.http_kernel.trace.event_subscriber')
            ->setArgument('$requestHeaders', $tracingConfigSection['request_headers'])
            ->setArgument('$responseHeaders', $tracingConfigSection['response_headers'])
            ->setArgument('$tracer', $this->getInstrumentationTracerOrDefaultTracer())
            ->addTag('kernel.event_subscriber');
    }

    protected function setMeteringDefinitions(): void
    {
        $this->container
            ->getDefinition('open_telemetry.instrumentation.http_kernel.metric.event_subscriber')
            ->addTag('kernel.event_subscriber')
            ->setArgument('$meter', $this->getInstrumentationMeterOrDefaultMeter())
            ->setArgument('$meterProvider', $this->getInstrumentationMeterProviderOrDefaultMeterProvider());
    }
}
