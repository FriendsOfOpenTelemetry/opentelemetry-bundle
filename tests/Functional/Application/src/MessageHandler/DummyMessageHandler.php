<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\Application\MessageHandler;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\Application\Message\DummyMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DummyMessageHandler
{
    public function __invoke(DummyMessage $message): string
    {
        return $message->name;
    }
}
