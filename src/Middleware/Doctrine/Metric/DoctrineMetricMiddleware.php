<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Middleware\Doctrine\Metric;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware;

class DoctrineMetricMiddleware implements Middleware
{
    public function wrap(Driver $driver): Driver
    {
        return new MetricConnectionDriverMiddleware($driver);
    }
}
