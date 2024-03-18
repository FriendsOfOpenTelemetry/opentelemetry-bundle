<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\Instrumentation\Symfony\Routing;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Attribute\Traceable;
use Symfony\Component\Routing\Attribute\Route;

class TraceableActionController
{
    #[Traceable('test')]
    #[Route('/path', name: 'action')]
    public function action(): void
    {
    }
}
