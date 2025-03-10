<?php

declare(strict_types=1);

namespace App\Controller\Traceable;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AutoTraceableController extends AbstractTraceableController
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
