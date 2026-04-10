<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Resource;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\Attributes\ServiceAttributes;
use OpenTelemetry\SemConv\Incubating\Attributes\DeploymentIncubatingAttributes;
use OpenTelemetry\SemConv\Incubating\Attributes\ServiceIncubatingAttributes;
use OpenTelemetry\SemConv\Version;

final class ResourceInfoFactory
{
    public static function create(string $namespace, string $name, string $version, string $environment): ResourceInfo
    {
        $resourceInfo = \OpenTelemetry\SDK\Resource\ResourceInfoFactory::defaultResource();

        return $resourceInfo->merge(ResourceInfo::create(Attributes::create([
            ServiceIncubatingAttributes::SERVICE_NAMESPACE => $namespace,
            ServiceAttributes::SERVICE_NAME => $name,
            ServiceAttributes::SERVICE_VERSION => $version,
            DeploymentIncubatingAttributes::DEPLOYMENT_ENVIRONMENT_NAME => $environment,
        ]), Version::VERSION_1_38_0->url()));
    }
}
