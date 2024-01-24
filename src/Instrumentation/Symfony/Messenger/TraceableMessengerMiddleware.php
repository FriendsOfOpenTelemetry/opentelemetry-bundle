<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Messenger;

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final readonly class TraceableMessengerMiddleware implements MiddlewareInterface
{
    public function __construct(
        private TracerInterface $tracer,
        private string $busName = 'default',
        private string $eventCategory = 'messenger.middleware'
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $scope = Context::storage()->scope();
        if (null === $scope) {
            return $stack->next()->handle($envelope, $stack);
        }

        $traceableStamp = $this->getTraceableStamp($envelope);
        if (null !== $traceableStamp && $traceableStamp->getSpan()->isRecording()) {
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

        $parent = Context::getCurrent();

        $span = $spanBuilder->setParent($parent)->startSpan();

        $stack = new TraceableMessengerStack(
            $span,
            $stack,
            $this->busName,
        );

        $envelope = $envelope->with(new TraceableStamp($span));

        try {
            return $stack->next()->handle($envelope, $stack);
        } finally {
            $stack->stop();
        }
    }

    private function getTraceableStamp(Envelope $envelope): ?TraceableStamp
    {
        return $envelope->last(TraceableStamp::class);
    }
}
