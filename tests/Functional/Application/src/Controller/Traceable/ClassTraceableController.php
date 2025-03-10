<?php

namespace App\Controller\Traceable;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Attribute\Traceable;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Traceable]
class ClassTraceableController extends AbstractTraceableController
{
    public function __construct(private readonly TracerInterface $tracer)
    {
    }

    #[Route('/class-traceable', methods: ['GET'])]
    public function index(): Response
    {
        return $this->json([
            'status' => 'ok',
        ]);
    }

    #[Route('/class-manual', methods: ['GET'])]
    public function manual(): Response
    {
        $spanBuilder = $this->tracer
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
