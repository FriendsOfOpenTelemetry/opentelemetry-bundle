<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Application\MessageHandler;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Application\Message\DummyMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DummyMessageHandler
{
    public function __invoke(DummyMessage $message): string
    {
        return $message->name;
    }
}
