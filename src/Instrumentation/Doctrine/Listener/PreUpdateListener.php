<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\Listener;

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
