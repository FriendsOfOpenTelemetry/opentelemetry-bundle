<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Context\Attribute;

enum DoctrineTraceAttributeEnum: string
{
    case User = 'doctrine.user';

    public function toString(): string
    {
        return $this->value;
    }
}
