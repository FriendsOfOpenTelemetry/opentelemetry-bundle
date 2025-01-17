<?php

namespace App\Message;

final readonly class ExceptionMessage
{
    public function __construct(
        public string $message,
    ) {
    }
}
