<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\EventListener\Doctrine\Trace;

use Doctrine\ORM\Event\PostRemoveEventArgs;
use OpenTelemetry\API\Trace\TracerInterface;

class PostRemoveListener
{
    public function __construct(
        private readonly TracerInterface $tracer,
    ) {
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
    }
}
