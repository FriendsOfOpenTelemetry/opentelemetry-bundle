<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry;

use OpenTelemetry\Contrib\Otlp\OtlpUtil;

final class HeadersHelper
{
    public static function getOpenTelemetryUserAgentHeaderValue(): string
    {
        return OtlpUtil::getUserAgentHeader()['User-Agent'];
    }
}
