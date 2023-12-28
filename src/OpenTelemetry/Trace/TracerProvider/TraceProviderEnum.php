<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TracerProvider;

use OpenTelemetry\SDK\Trace\NoopTracerProvider;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SDK\Trace\TracerProviderInterface;

enum TraceProviderEnum: string
{
    case Default = 'default';
    case Noop = 'noop';
    //    case Traceable = 'traceable';

    /**
     * @return class-string<TracerProviderFactoryInterface>
     */
    public function getFactoryClass(): string
    {
        return match ($this) {
            self::Default => TracerProviderFactory::class,
            self::Noop => NoopTracerProviderFactory::class,
        };
    }

    /**
     * @return class-string<TracerProviderInterface>
     */
    public function getClass(): string
    {
        return match ($this) {
            self::Default => TracerProvider::class,
            self::Noop => NoopTracerProvider::class,
        };
    }
}
