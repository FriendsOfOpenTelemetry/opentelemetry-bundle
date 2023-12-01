<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle;

use Composer\InstalledVersions;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class OpenTelemetryBundle extends Bundle
{
    public static function name(): string
    {
        return 'friendsofopentelemetry/opentelemetry-bundle';
    }

    public static function version(): string
    {
        return InstalledVersions::getPrettyVersion(self::name());
    }
}
