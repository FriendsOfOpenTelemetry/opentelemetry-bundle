<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Messenger;

use OpenTelemetry\API\Trace\SpanInterface;
use Symfony\Component\Messenger\Stamp\StampInterface;

final readonly class TraceableStamp implements StampInterface
{
    public function __construct(private SpanInterface $span)
    {
    }

    public function getSpan(): SpanInterface
    {
        return $this->span;
    }
}
