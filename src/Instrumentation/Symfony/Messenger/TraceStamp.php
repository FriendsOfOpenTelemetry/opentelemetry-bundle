<?php

declare(strict_types=1);

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Messenger;

use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * @doc: https://www.w3.org/TR/trace-context/
 *
 * You can see how the trace parent generated here: https://github.com/open-telemetry/opentelemetry-php/blob/main/src/API/Trace/Propagation/TraceContextPropagator.php
 */
readonly class TraceStamp implements StampInterface
{
    public function __construct(
        private string $traceParent,
    ) {
    }

    public function getTraceParent(): string
    {
        return $this->traceParent;
    }
}
