<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\EventListener\Doctrine\Trace;

use Doctrine\ORM\Event\OnClearEventArgs;
use OpenTelemetry\API\Trace\TracerInterface;

class OnClearListener
{
    public function __construct(
        private readonly TracerInterface $tracer,
    ) {
    }

    public function onClear(OnClearEventArgs $args): void
    {
    }
}
