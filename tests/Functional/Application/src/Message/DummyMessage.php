<?php

namespace App\Message;

final readonly class DummyMessage
{
    public function __construct(
        public string $name,
    ) {
    }
}
