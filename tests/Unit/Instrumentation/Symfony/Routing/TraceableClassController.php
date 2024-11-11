<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\Instrumentation\Symfony\Routing;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Attribute\Traceable;
use Symfony\Component\Routing\Attribute\Route;

#[Traceable('test')]
class TraceableClassController
{
    #[Route('/traceable-class', name: 'action')]
    public function index(): void
    {
    }
}
