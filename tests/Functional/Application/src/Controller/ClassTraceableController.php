<?php

namespace App\Controller;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Attribute\Traceable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Traceable]
class ClassTraceableController extends AbstractController
{
    #[Route('/class-traceable', methods: ['GET'])]
    public function index(): Response
    {
        return $this->json([
            'status' => 'ok',
        ]);
    }
}
