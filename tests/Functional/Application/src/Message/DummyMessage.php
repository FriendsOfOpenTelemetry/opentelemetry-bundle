<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\Application\Message;

final readonly class DummyMessage
{
    public function __construct(
        public string $name,
    ) {
    }
}
