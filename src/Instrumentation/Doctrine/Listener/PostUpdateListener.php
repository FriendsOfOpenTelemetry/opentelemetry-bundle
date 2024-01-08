<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\Listener;

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
