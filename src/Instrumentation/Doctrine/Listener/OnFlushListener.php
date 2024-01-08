<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\Listener;

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
