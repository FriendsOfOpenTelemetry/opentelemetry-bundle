<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation;

enum InstrumentationTypeEnum: string
{
    case Auto = 'auto';
    case Attribute = 'attribute';
}
