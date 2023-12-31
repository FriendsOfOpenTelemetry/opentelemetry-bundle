<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Context\Attribute;

enum HttpKernelTraceAttributeEnum: string
{
    case HttpHost = 'symfony.kernel.http.host';
    case HttpUserAgent = 'symfony.kernel.http.user_agent';
    case NetHostIp = 'symfony.kernel.net.host_ip';
    case NetPeerIp = 'symfony.kernel.net.peer_ip';

    public function toString(): string
    {
        return $this->value;
    }
}
