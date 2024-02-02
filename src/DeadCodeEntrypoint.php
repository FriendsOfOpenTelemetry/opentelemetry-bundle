<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\OpenTelemetryExtension;
use PHPStan\Reflection\ReflectionProvider;
use PHPUnit\Framework\TestCase;
use ShipMonk\PHPStan\DeadCode\Provider\EntrypointProvider;

class DeadCodeEntrypoint implements EntrypointProvider
{
    public function __construct(
        private ReflectionProvider $reflectionProvider
    ) {
    }

    public function isEntrypoint(\ReflectionMethod $method): bool
    {
        $methodName = $method->getName();
        $reflection = $this->reflectionProvider->getClass($method->getDeclaringClass()->getName());

        return ($reflection->is(OpenTelemetryBundle::class)
            || $reflection->is(OpenTelemetryExtension::class))
            && false === $reflection->isSubclassOf(TestCase::class);
    }
}
