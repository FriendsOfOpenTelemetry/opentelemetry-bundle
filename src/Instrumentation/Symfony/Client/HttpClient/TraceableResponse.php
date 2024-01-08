<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Client\HttpClient;

use Symfony\Component\HttpClient\Response\StreamableInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class TraceableResponse implements ResponseInterface, StreamableInterface
{
    public function getStatusCode(): int
    {
        // TODO: Implement getStatusCode() method.
    }

    public function getHeaders(bool $throw = true): array
    {
        // TODO: Implement getHeaders() method.
    }

    public function getContent(bool $throw = true): string
    {
        // TODO: Implement getContent() method.
    }

    public function toArray(bool $throw = true): array
    {
        // TODO: Implement toArray() method.
    }

    public function cancel(): void
    {
        // TODO: Implement cancel() method.
    }

    public function getInfo(string $type = null): mixed
    {
        // TODO: Implement getInfo() method.
    }
}
