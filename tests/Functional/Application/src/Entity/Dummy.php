<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\Application\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\Application\Repository\DummyRepository;

#[Entity(repositoryClass: DummyRepository::class)]
#[Table]
final class Dummy
{
    public function __construct(
        #[Id]
        #[GeneratedValue]
        #[Column(type: Types::INTEGER)]
        public ?int $id = null,

        #[Column(type: Types::STRING)]
        public ?string $name = null,
    ) {
    }
}
