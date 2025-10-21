<?php

declare(strict_types=1);

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterEndpointInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use RdKafka\Conf;

final readonly class KafkaTransportFactory implements TransportFactoryInterface
{
    public function supports(#[\SensitiveParameter] ExporterEndpointInterface $endpoint, ExporterOptionsInterface $options): bool
    {
        return TransportEnum::Kafka === TransportEnum::tryFrom($endpoint->getTransport());
    }

    public function createTransport(#[\SensitiveParameter] ExporterEndpointInterface $endpoint, ExporterOptionsInterface $options): TransportInterface
    {
        $dsn = $endpoint->getDsn();
        $queryParameters = $dsn->getQuery()->all();
        $conf = new Conf();
        foreach ($queryParameters as $k => $v) {
            $conf->set(\str_replace('_', '.', $k), (string) $v);
        }

        return new KafkaTransport($conf, $dsn->getHost());
    }
}
