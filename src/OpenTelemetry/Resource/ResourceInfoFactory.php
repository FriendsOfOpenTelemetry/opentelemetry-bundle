<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Resource;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\ResourceAttributes;

final class ResourceInfoFactory
{
    public static function create(string $namespace, string $name, string $version, string $environment): ResourceInfo
    {
        $resourceInfo = \OpenTelemetry\SDK\Resource\ResourceInfoFactory::defaultResource();

        return $resourceInfo->merge(ResourceInfo::create(Attributes::create([
            ResourceAttributes::SERVICE_NAMESPACE => $namespace,
            ResourceAttributes::SERVICE_NAME => $name,
            ResourceAttributes::SERVICE_VERSION => $version,
            ResourceAttributes::DEPLOYMENT_ENVIRONMENT_NAME => $environment,
        ]), ResourceAttributes::SCHEMA_URL));
    }
}
