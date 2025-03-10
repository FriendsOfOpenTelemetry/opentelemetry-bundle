<?php

namespace App\Controller\Traceable;

use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AutowireTracerController extends AbstractTraceableController
{
    public function __construct(
        #[Autowire('@open_telemetry.traces.tracers.fallback')]
        private readonly TracerInterface $tracer,
    ) {
    }

    #[Route('/autowire-tracer', methods: ['GET'])]
    public function index(): Response
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
