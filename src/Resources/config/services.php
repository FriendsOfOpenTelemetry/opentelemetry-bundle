<?php

use GaelReyrol\OpenTelemetryBundle\EventSubscriber\ConsoleEventSubscriber;
use GaelReyrol\OpenTelemetryBundle\EventSubscriber\HttpKernelEventSubscriber;
use GaelReyrol\OpenTelemetryBundle\OpenTelemetryBundle;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('open_telemetry.bundle.name', OpenTelemetryBundle::name())
        ->set('open_telemetry.bundle.version', OpenTelemetryBundle::version())
    ;

    $container->services()
        ->defaults()
        ->private()

        ->set('open_telemetry.instrumentation.http_kernel.event_subscriber', HttpKernelEventSubscriber::class)
        ->set('open_telemetry.instrumentation.console.event_subscriber', ConsoleEventSubscriber::class)

        ->set('open_telemetry.traces.exporter', SpanExporterInterface::class)
        ->set('open_telemetry.traces.processor', SpanProcessorInterface::class)
        ->set('open_telemetry.traces.sampler', SamplerInterface::class)
        ->set('open_telemetry.traces.provider', TracerInterface::class)
    ;
};
