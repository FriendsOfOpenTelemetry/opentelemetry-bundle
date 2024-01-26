<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterEndpointInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use OpenTelemetry\SDK\Common\Export\TransportInterface;

final class TransportFactory implements TransportFactoryInterface
{
    /**
     * @param iterable<mixed, TransportFactoryInterface> $factories
     */
    public function __construct(private readonly iterable $factories)
    {
    }

    public function supports(#[\SensitiveParameter] ExporterEndpointInterface $endpoint, ExporterOptionsInterface $options): bool
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($endpoint, $options)) {
                return true;
            }
        }

        return false;
    }

    public function createTransport(#[\SensitiveParameter] ExporterEndpointInterface $endpoint, ExporterOptionsInterface $options): TransportInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($endpoint, $options)) {
                return $factory->createTransport($endpoint, $options);
            }
        }

        throw new \InvalidArgumentException('No transport supports the given endpoint.');
    }
}
