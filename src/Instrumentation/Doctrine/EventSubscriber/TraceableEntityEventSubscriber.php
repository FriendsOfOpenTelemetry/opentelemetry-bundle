<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnClearEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use OpenTelemetry\API\Trace\TracerInterface;

final class TraceableEntityEventSubscriber implements EventSubscriber
{
    public function __construct(
        private readonly TracerInterface $tracer,
    ) {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::onClear,
            Events::onFlush,
            Events::postFlush,
            Events::postLoad,
            Events::postPersist,
            Events::postRemove,
            Events::postUpdate,
            Events::preFlush,
            Events::prePersist,
            Events::preRemove,
            Events::preUpdate,
        ];
    }

    public function onClear(OnClearEventArgs $args): void
    {
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
    }

    public function postLoad(PostLoadEventArgs $args): void
    {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
    }

    public function preFlush(PreFlushEventArgs $args): void
    {
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
    }
}
