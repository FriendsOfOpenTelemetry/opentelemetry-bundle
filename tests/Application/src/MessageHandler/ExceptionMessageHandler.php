<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Application\MessageHandler;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Application\Message\ExceptionMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ExceptionMessageHandler
{
    public function __invoke(ExceptionMessage $message): void
    {
        throw new \RuntimeException($message->message);
    }
}
