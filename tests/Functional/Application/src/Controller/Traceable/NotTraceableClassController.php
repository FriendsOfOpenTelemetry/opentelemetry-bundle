<?php

namespace App\Controller\Traceable;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NotTraceableClassController extends AbstractTraceableController
{
    #[Route('/not-traceable-class', methods: ['GET'])]
    public function index(): Response
    {
        return new Response(null, Response::HTTP_FOUND);
    }
}
