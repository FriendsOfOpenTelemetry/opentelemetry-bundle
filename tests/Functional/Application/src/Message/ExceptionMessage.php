<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\Application\Message;

final readonly class ExceptionMessage
{
    public function __construct(
        public string $message,
    ) {
    }
}
