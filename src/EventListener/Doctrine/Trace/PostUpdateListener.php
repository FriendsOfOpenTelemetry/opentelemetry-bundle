<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\EventListener\Doctrine\Trace;

use Doctrine\ORM\Event\PostUpdateEventArgs;
use OpenTelemetry\API\Trace\TracerInterface;

class PostUpdateListener
{
    public function __construct(
        private readonly TracerInterface $tracer,
    ) {
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
    }
}
