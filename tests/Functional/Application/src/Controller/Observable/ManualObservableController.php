<?php

declare(strict_types=1);

namespace App\Controller\Observable;

use OpenTelemetry\API\Metrics\MeterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ManualObservableController extends AbstractObservableController
{
    public function __construct(
        private readonly MeterInterface $meter,
    ) {
    }

    #[Route('/manual-observable')]
    public function index(): JsonResponse
    {
        $counter = $this->meter->createCounter('manual');

        $counter->add(1);

        return $this->json(['status' => 'ok']);
    }
}
