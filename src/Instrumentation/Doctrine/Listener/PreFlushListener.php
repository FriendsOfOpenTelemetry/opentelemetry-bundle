<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\Listener;

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
