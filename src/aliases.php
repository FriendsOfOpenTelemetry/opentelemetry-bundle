<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle;

use Doctrine\DBAL\Result;
use Doctrine\DBAL\VersionAwarePlatformDriver;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\Middleware\TraceableConnection;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\Middleware\TraceableConnectionV3;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\Middleware\TraceableConnectionV4;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\Middleware\TraceableDriver;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\Middleware\TraceableDriverV3;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\Middleware\TraceableDriverV4;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\Middleware\TraceableStatement;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\Middleware\TraceableStatementV3;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\Middleware\TraceableStatementV4;

if (!class_exists(TraceableStatement::class)) {
    if (class_exists(Result::class) && !interface_exists(VersionAwarePlatformDriver::class)) {
        class_alias(TraceableStatementV4::class, TraceableStatement::class);
        class_alias(TraceableConnectionV4::class, TraceableConnection::class);
        class_alias(TraceableDriverV4::class, TraceableDriver::class);
    } elseif (class_exists(Result::class)) {
        class_alias(TraceableStatementV3::class, TraceableStatement::class);
        class_alias(TraceableConnectionV3::class, TraceableConnection::class);
        class_alias(TraceableDriverV3::class, TraceableDriver::class);
    }
}
