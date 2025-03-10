<?php

namespace App\Command\Traceable;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Attribute\Traceable;
use Symfony\Component\Console\Attribute\AsCommand;

#[Traceable(tracer: 'open_telemetry.traces.tracers.fallback')]
#[AsCommand('traceable:fallback-command')]
class FallbackCommand extends TraceableCommand
{
}
