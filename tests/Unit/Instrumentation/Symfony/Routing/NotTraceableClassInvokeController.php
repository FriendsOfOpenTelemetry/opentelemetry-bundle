<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\Instrumentation\Symfony\Routing;

use Symfony\Component\Routing\Attribute\Route;

class NotTraceableClassInvokeController
{
    #[Route('/not-traceable-class-invoke', name: 'action')]
    public function __invoke(): void
    {
    }
}
