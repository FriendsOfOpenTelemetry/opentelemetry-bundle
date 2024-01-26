<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider;

use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\LoggerProviderInterface;
use OpenTelemetry\SDK\Logs\NoopLoggerProvider;

enum LoggerProviderEnum: string
{
    case Default = 'default';
    case Noop = 'noop';

    /**
     * @return class-string<LoggerProviderFactoryInterface>
     */
    public function getFactoryClass(): string
    {
        return match ($this) {
            self::Default => DefaultLoggerProviderFactory::class,
            self::Noop => NoopLoggerProviderFactory::class,
        };
    }

    /**
     * @return class-string<LoggerProviderInterface>
     */
    public function getClass(): string
    {
        return match ($this) {
            self::Default => LoggerProvider::class,
            self::Noop => NoopLoggerProvider::class,
        };
    }
}
