<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Application;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetryBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new OpenTelemetryBundle(),
        ];
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }
}
