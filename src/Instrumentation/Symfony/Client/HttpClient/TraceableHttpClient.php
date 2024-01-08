<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Client\HttpClient;

use Psr\Log\LoggerAwareInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;
use Symfony\Contracts\Service\ResetInterface;

final class TraceableHttpClient implements HttpClientInterface, LoggerAwareInterface, ResetInterface
{
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        // TODO: Implement request() method.
    }

    public function stream(iterable|ResponseInterface $responses, float $timeout = null): ResponseStreamInterface
    {
        // TODO: Implement stream() method.
    }

    public function withOptions(array $options): static
    {
        // TODO: Implement withOptions() method.
    }
}
