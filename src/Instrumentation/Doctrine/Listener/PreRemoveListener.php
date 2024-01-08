<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\Listener;

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
