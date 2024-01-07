<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\EventListener\Doctrine\Trace;

use Doctrine\ORM\Event\PostPersistEventArgs;
use OpenTelemetry\API\Trace\TracerInterface;

class PostPersistListener
{
    public function __construct(
        private readonly TracerInterface $tracer,
    ) {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
    }
}
