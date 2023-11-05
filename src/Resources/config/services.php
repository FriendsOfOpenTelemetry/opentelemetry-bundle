<?php

use GaelReyrol\OpenTelemetryBundle\EventSubscriber\ConsoleEventSubscriber;
use GaelReyrol\OpenTelemetryBundle\EventSubscriber\KernelEventSubscriber;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
        ->private()

        ->set('open_telemetry.tracing.kernel_event_subscriber', KernelEventSubscriber::class)
        ->tag('kernel.event_subscriber')

        ->set('open_telemetry.tracing.console_event_subscriber', ConsoleEventSubscriber::class)
        ->tag('kernel.event_subscriber')
    ;
};
