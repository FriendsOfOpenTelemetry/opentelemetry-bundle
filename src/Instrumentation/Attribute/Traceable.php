<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class Traceable
{
    public function __construct(
        public readonly ?string $tracer = null,
    ) {
    }
}
