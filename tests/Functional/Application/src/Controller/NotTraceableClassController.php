<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NotTraceableClassController extends AbstractController
{
    #[Route('/not-traceable-class', methods: ['GET'])]
    public function index(): Response
    {
        return new Response(null, Response::HTTP_FOUND);
    }
}
