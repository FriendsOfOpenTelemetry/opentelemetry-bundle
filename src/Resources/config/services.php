<?php

use GaelReyrol\OpenTelemetryBundle\EventSubscriber\ConsoleEventSubscriber;
use GaelReyrol\OpenTelemetryBundle\EventSubscriber\KernelEventSubscriber;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
        ->private()

        ->set('open_telemetry.instrumentation.kernel.event_subscriber', KernelEventSubscriber::class)
        ->set('open_telemetry.instrumentation.console.event_subscriber', ConsoleEventSubscriber::class)
    ;
};
