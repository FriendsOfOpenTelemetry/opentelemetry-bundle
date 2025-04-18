<?php

declare(strict_types=1);

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

        if ($key !== TraceContextPropagator::TRACEPARENT) {
            return;
        }

        $carrier = $carrier->with(new TraceStamp($value));
    }
}
