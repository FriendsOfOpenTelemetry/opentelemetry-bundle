<?php

declare(strict_types=1);

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Messenger\Amqp;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Context\Propagator\TraceStampPropagator;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\Propagation\MultiTextMapPropagator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
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
    ) {}

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if ($envelope->last(AmqpStamp::class) !== null) {
            $this->onMessageSent($envelope);
        }

        return $stack->next()->handle($envelope, $stack);
    }

    private function onMessageSent(Envelope &$envelope): void
    {
        $scope = Context::storage()->scope();

        if ($scope === null) {
            $this->logger?->debug('No active scope');
        }

        $this->propagator->inject($envelope, new TraceStampPropagator(), Context::getCurrent());
        $this->logger?->debug('Trace stamp added to envelope for propagation');
    }
}
