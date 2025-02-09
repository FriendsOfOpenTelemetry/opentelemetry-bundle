<?php

namespace App\Controller;

use App\Entity\Dummy;
use Doctrine\ORM\EntityManagerInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Attribute\Traceable;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ActionTraceableController extends AbstractController
{
    #[Traceable]
    #[Route('/ok', methods: ['GET'])]
    public function ok(): Response
    {
        return $this->json([
            'status' => 'ok',
        ]);
    }

    #[Traceable]
    #[Route('/failure', methods: ['GET'])]
    public function failure(): Response
    {
        return $this->json([
            'status' => 'failure',
        ], Response::HTTP_SERVICE_UNAVAILABLE);
    }

    #[Traceable]
    #[Route('/exception', methods: ['GET'])]
    public function exception(): Response
    {
        throw new \RuntimeException('Oops');
    }

    #[Traceable]
    #[Route('/view', methods: ['GET'])]
    public function view(): Response
    {
        return $this->render('dummy.html.twig');
    }

    #[Traceable]
    #[Route('/fragment', methods: ['GET'])]
    public function segment(): Response
    {
        return $this->render('fragment.html.twig');
    }

    #[Route('/dummy/{dummy}', methods: ['GET'])]
    public function viewDummy(Dummy $dummy): Response
    {
        return $this->json([
            'id' => $dummy->id,
            'name' => $dummy->name,
        ]);
    }

    #[Route('/dummy', methods: ['POST'])]
    public function createDummy(EntityManagerInterface $entityManager, Request $request): Response
    {
        $name = $request->get('name');

        $dummy = new Dummy(name: $name);
        $entityManager->persist($dummy);
        $entityManager->flush();

        return $this->json([
            'id' => $dummy->id,
            'name' => $dummy->name,
        ]);
    }

    #[Route('/not-traceable', methods: ['GET'])]
    public function notTraceable(): Response
    {
        return new Response(null, Response::HTTP_FOUND);
    }

    #[Traceable]
    #[Route('/manual-action', methods: ['GET'])]
    public function manual(TracerInterface $tracer): Response
    {
        $spanBuilder = $tracer
            ->spanBuilder('Manual')
            ->setAttributes([
                TraceAttributes::CODE_FUNCTION_NAME => 'manual',
                TraceAttributes::CODE_NAMESPACE => self::class,
            ]);

        $parent = Context::getCurrent();

        $span = $spanBuilder->setParent($parent)->startSpan();
        sleep(1);
        $span->addEvent('sleep', [
            'sleep.duration' => '1s',
        ]);
        $span->setStatus(StatusCode::STATUS_OK);
        $span->end();

        return $this->json([
            'status' => 'ok',
        ]);
    }
}
