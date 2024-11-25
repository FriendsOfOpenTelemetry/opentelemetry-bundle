<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\Resource;

use OpenTelemetry\SDK\Resource\ResourceInfo;

interface ResourceInfoFactoryInterface
{
    public function create(): ResourceInfo;
}
