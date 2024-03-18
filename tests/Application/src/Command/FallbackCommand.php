<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Application\Command;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Attribute\Traceable;
use Symfony\Component\Console\Attribute\AsCommand;

#[Traceable(tracer: 'open_telemetry.traces.tracers.fallback')]
#[AsCommand('fallback-command')]
class FallbackCommand extends TraceableCommand
{
}
