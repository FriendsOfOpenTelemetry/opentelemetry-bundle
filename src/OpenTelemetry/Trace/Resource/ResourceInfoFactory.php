<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\Resource;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory as OtelResourceInfoFactory;

final class ResourceInfoFactory implements ResourceInfoFactoryInterface
{
    public function create(): ResourceInfo {
        $default = OtelResourceInfoFactory::defaultResource();

        return $default->merge(ResourceInfo::create(Attributes::create([
            'service.name' => 'bundle.override',
        ])));
    }
}
