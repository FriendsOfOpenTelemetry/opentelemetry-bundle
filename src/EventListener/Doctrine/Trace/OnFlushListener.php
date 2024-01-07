<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\EventListener\Doctrine\Trace;

use Doctrine\ORM\Event\OnFlushEventArgs;
use OpenTelemetry\API\Trace\TracerInterface;

class OnFlushListener
{
    public function __construct(
        private readonly TracerInterface $tracer,
    ) {
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
    }
}
