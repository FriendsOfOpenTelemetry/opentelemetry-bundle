<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation;

interface InstrumentationTypeInterface
{
    public function setInstrumentationType(InstrumentationTypeEnum $type): void;
}
