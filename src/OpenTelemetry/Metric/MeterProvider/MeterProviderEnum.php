<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider;

use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Metrics\NoopMeterProvider;

enum MeterProviderEnum: string
{
    case Noop = 'noop';
    case Default = 'default';

    /**
     * @return class-string<MeterProviderFactoryInterface>
     */
    public function getFactoryClass(): string
    {
        return match ($this) {
            self::Default => MeterProviderFactory::class,
            self::Noop => NoopMeterProviderFactory::class,
        };
    }

    /**
     * @return class-string<MeterProviderInterface>
     */
    public function getClass(): string
    {
        return match ($this) {
            self::Default => MeterProvider::class,
            self::Noop => NoopMeterProvider::class,
        };
    }
}
