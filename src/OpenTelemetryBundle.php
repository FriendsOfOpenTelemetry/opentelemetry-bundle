<?php

namespace GaelReyrol\OpenTelemetryBundle;

use Composer\InstalledVersions;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class OpenTelemetryBundle extends Bundle
{
    public static function name(): string
    {
        return 'gaelreyrol/opentelemetry-bundle';
    }

    public static function version(): string
    {
        return InstalledVersions::getPrettyVersion(self::name());
    }
}
