<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\EventListener\Doctrine\Trace;

use Doctrine\ORM\Event\PreRemoveEventArgs;
use OpenTelemetry\API\Trace\TracerInterface;

class PreRemoveListener
{
    public function __construct(
        private readonly TracerInterface $tracer,
    ) {
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
    }
}
