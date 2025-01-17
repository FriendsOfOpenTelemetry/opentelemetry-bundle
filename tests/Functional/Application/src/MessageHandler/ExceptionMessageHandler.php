<?php

namespace App\MessageHandler;

use App\Message\ExceptionMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ExceptionMessageHandler
{
    public function __invoke(ExceptionMessage $message): void
    {
        throw new \RuntimeException($message->message);
    }
}
