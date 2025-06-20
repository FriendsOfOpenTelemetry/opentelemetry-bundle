<?php

use App\Controller\Traceable\ActionTraceableController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator->add('php-config', '/php-config')
        ->controller([ActionTraceableController::class, 'phpConfig'])
        ->methods(['GET']);
};
