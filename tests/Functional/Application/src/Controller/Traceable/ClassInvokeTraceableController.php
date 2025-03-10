<?php

namespace App\Controller\Traceable;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Attribute\Traceable;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Traceable]
class ClassInvokeTraceableController extends AbstractTraceableController
{
    #[Route('/class-invoke-traceable', methods: ['GET'])]
    public function __invoke(): Response
    {
        return $this->json([
            'status' => 'ok',
        ]);
    }
}
