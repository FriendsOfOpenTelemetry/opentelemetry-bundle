<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\Instrumentation\Symfony\Routing;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Attribute\Traceable;
use Symfony\Component\Routing\Attribute\Route;

#[Traceable('test')]
class TraceableClassInvokeController
{
    #[Route('/traceable-class-invoke', name: 'action')]
    public function __invoke(): void
    {
    }
}
