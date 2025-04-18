<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Messenger;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Context\Propagator\TraceStampPropagator;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\Propagation\MultiTextMapPropagator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

/**
 * Be aware the app consuming the message must be able to denormalize the stamp.
 */
readonly class AddStampForPropagationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private MultiTextMapPropagator $propagator,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $traceStamp = $envelope->last(TraceStamp::class);

        if (null !== $traceStamp) {
            return $stack->next()->handle($envelope, $stack);
        }

        $scope = Context::storage()->scope();

        if (null !== $scope) {
            $this->propagator->inject($envelope, new TraceStampPropagator($this->logger), Context::getCurrent());
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
