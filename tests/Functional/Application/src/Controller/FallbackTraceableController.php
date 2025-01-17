<?php

namespace App\Controller;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Attribute\Traceable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Traceable(tracer: 'open_telemetry.traces.tracers.fallback')]
class FallbackTraceableController extends AbstractController
{
    #[Route('/fallback-traceable', methods: ['GET'])]
    public function __invoke(): Response
    {
        return $this->json([
            'status' => 'ok',
        ]);
    }
}
