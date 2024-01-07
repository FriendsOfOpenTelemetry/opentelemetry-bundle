<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\EventListener\Doctrine\Trace;

use Doctrine\ORM\Event\PrePersistEventArgs;
use OpenTelemetry\API\Trace\TracerInterface;

class PrePersistListener
{
    public function __construct(
        private readonly TracerInterface $tracer,
    ) {
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
    }
}
