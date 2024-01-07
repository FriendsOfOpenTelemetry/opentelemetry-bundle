<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\EventListener\Doctrine\Trace;

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
