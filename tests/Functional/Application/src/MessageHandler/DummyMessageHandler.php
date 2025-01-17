<?php

namespace App\MessageHandler;

use App\Message\DummyMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DummyMessageHandler
{
    public function __invoke(DummyMessage $message): string
    {
        return $message->name;
    }
}
