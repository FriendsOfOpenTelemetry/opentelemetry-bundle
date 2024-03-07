<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Messenger;

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ScopeInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class TraceableMessengerMiddleware implements MiddlewareInterface
{
    private ?ScopeInterface $scope = null;

    public function __construct(
        private TracerInterface $tracer,
        private string $busName = 'default',
        private string $eventCategory = 'messenger.middleware'
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $scope = Context::storage()->scope();

        $traceableStamp = $this->getTraceableStamp($envelope);
        if (null !== $traceableStamp && $traceableStamp->getSpan()->isRecording()) {
            $this->scope?->detach();
            $this->scope = null;

            $span = $traceableStamp->getSpan();
            $span->setStatus(StatusCode::STATUS_OK);
            $span->end();
        }

        $spanBuilder = $this->tracer
            ->spanBuilder('messenger.middleware')
            ->setSpanKind(SpanKind::KIND_INTERNAL)
            ->setAttribute('event.category', $this->eventCategory)
            ->setAttribute('bus.name', $this->busName)
        ;

        $span = $spanBuilder->setParent($scope?->context())->startSpan();
        if (null === $scope && null === $this->scope) {
            $this->scope = $span->storeInContext(Context::getCurrent())->activate();
        }

        $stack = new TraceableMessengerStack(
            $span,
            $stack,
            $this->busName,
        );

        $envelope = $envelope->with(new TraceableStamp($span));

        try {
            return $stack->next()->handle($envelope, $stack);
        } finally {
            $this->scope?->detach();
            $this->scope = null;
            $stack->stop();
        }
    }

    private function getTraceableStamp(Envelope $envelope): ?TraceableStamp
    {
        return $envelope->last(TraceableStamp::class);
    }
}
