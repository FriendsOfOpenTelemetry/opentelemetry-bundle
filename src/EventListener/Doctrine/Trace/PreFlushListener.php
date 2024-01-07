<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\EventListener\Doctrine\Trace;

use Doctrine\ORM\Event\PreFlushEventArgs;
use OpenTelemetry\API\Trace\TracerInterface;

class PreFlushListener
{
    public function __construct(
        private readonly TracerInterface $tracer,
    ) {
    }

    public function preFlush(PreFlushEventArgs $args): void
    {
    }
}
