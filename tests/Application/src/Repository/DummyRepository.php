<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Application\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Application\Entity\Dummy;

/**
 * @extends ServiceEntityRepository<Dummy>
 */
class DummyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dummy::class);
    }
}
