<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\EventListener\Doctrine\Trace;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use OpenTelemetry\API\Trace\TracerInterface;

class PreUpdateListener
{
    public function __construct(
        private readonly TracerInterface $tracer,
    ) {
    }

    public function preRemove(PreUpdateEventArgs $args): void
    {
    }
}
