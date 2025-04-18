<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Context\Propagator;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Messenger\TraceStamp;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\Context\Propagation\PropagationSetterInterface;
use Symfony\Component\Messenger\Envelope;

class TraceStampPropagator implements PropagationSetterInterface
{
    /**
     * @param mixed $carrier
     */
    public function set(&$carrier, string $key, string $value): void
    {
        if (!$carrier instanceof Envelope) {
            throw new \InvalidArgumentException(sprintf('The carrier for trace stamp propagation must be instance of %s', Envelope::class));
        }

        if (TraceContextPropagator::TRACEPARENT !== $key) {
            return;
        }

        $carrier = $carrier->with(new TraceStamp($value));
    }
}
