<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\Listener;

use Doctrine\ORM\Event\PostLoadEventArgs;
use OpenTelemetry\API\Trace\TracerInterface;

class PostLoadListener
{
    public function __construct(
        private readonly TracerInterface $tracer,
    ) {
    }

    public function postLoad(PostLoadEventArgs $args): void
    {
    }
}
