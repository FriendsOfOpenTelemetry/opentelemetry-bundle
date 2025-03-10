<?php

namespace App\Controller\Traceable;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Attribute\Traceable;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Traceable(tracer: 'open_telemetry.traces.tracers.fallback')]
class FallbackTraceableController extends AbstractTraceableController
{
    #[Route('/fallback-traceable', methods: ['GET'])]
    public function __invoke(): Response
    {
        return $this->json([
            'status' => 'ok',
        ]);
    }
}
