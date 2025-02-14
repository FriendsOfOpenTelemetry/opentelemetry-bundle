<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AutoTraceableController extends AbstractController
{
    #[Route('/auto-traceable')]
    public function index(): Response
    {
        return $this->json([
            'status' => 'ok',
        ]);
    }

    #[Route('/auto-exclude')]
    public function exclude(): Response
    {
        return $this->json([
            'status' => 'ok',
        ]);
    }
}
