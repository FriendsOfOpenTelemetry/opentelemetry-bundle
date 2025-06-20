<?php

namespace App\Controller\Traceable;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Attribute\Traceable;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Traceable]
class DualTracerController extends AbstractTraceableController
{
    #[Route('/fallback-dual-tracer', methods: ['GET'])]
    public function fallback(
        #[Autowire('@open_telemetry.traces.tracers.fallback')]
        TracerInterface $tracer,
    ): Response {
        $spanBuilder = $tracer
            ->spanBuilder('Manual')
            ->setAttributes([
                TraceAttributes::CODE_FUNCTION_NAME => self::class.'::fallback',
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

    #[Route('/main-dual-tracer', methods: ['GET'])]
    public function main(
        #[Autowire('@open_telemetry.traces.tracers.main')]
        TracerInterface $tracer,
    ): Response {
        $spanBuilder = $tracer
            ->spanBuilder('Manual')
            ->setAttributes([
                TraceAttributes::CODE_FUNCTION_NAME => self::class.'::main',
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
