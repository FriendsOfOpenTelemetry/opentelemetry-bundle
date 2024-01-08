<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\Listener;

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
