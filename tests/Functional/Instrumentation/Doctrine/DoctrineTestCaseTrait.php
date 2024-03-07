<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\Instrumentation\Doctrine;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\VersionAwarePlatformDriver;

trait DoctrineTestCaseTrait
{
    protected static function isDoctrineDBALInstalled(): bool
    {
        return interface_exists(Driver::class);
    }

    protected static function isDoctrineDBALVersion3Installed(): bool
    {
        return self::isDoctrineDBALInstalled() && interface_exists(VersionAwarePlatformDriver::class);
    }

    protected static function isDoctrineDBALVersion4Installed(): bool
    {
        return self::isDoctrineDBALInstalled()
            && !self::isDoctrineDBALVersion3Installed()
            && !interface_exists(VersionAwarePlatformDriver::class);
    }
}
