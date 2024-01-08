<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\Listener;

use Doctrine\ORM\Event\PostFlushEventArgs;
use OpenTelemetry\API\Trace\TracerInterface;

class PostFlushListener
{
    public function __construct(
        private readonly TracerInterface $tracer,
    ) {
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
    }
}
