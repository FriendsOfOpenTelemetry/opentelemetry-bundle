<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Context\Propagator;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Messenger\TraceStamp;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\Context\Propagation\PropagationGetterInterface;
use OpenTelemetry\Context\Propagation\PropagationSetterInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;

readonly class TraceStampPropagator implements PropagationSetterInterface, PropagationGetterInterface
{
    public function __construct(
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function set(&$carrier, string $key, string $value): void
    {
        if (!$carrier instanceof Envelope) {
            throw new \InvalidArgumentException(sprintf('The carrier for trace stamp propagation must be instance of %s', Envelope::class));
        }

        if (TraceContextPropagator::TRACEPARENT !== $key) {
            return;
        }

        $carrier = $carrier->with(new TraceStamp($value));
        $this->logger?->debug("Trace stamp added to envelope for propagation with value: $value");
    }

    public function keys($carrier): array
    {
        if (!$carrier instanceof Envelope) {
            throw new \InvalidArgumentException(sprintf('The carrier for trace stamp propagation must be instance of %s', Envelope::class));
        }

        return [TraceContextPropagator::TRACEPARENT];
    }

    public function get($carrier, string $key): ?string
    {
        if (!$carrier instanceof Envelope) {
            throw new \InvalidArgumentException(sprintf('The carrier for trace stamp propagation must be instance of %s', Envelope::class));
        }

        if (TraceContextPropagator::TRACEPARENT !== $key) {
            return null;
        }

        $traceStamp = $carrier->last(TraceStamp::class);

        if (null === $traceStamp) {
            return null;
        }

        $traceParent = $traceStamp->getTraceParent();
        $this->logger?->debug("Get trace parent from TraceStamp with value: $traceParent");

        return $traceParent;
    }
}
