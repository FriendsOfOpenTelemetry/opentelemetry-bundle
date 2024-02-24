<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Application\Message;

final readonly class ExceptionMessage
{
    public function __construct(
        public string $message,
    ) {
    }
}
