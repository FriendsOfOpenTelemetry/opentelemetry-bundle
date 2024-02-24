<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Application\Message;

final readonly class DummyMessage
{
    public function __construct(
        public string $name,
    ) {
    }
}
