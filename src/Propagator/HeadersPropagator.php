<?php

namespace GaelReyrol\OpenTelemetryBundle\Propagator;

use OpenTelemetry\Context\Propagation\PropagationGetterInterface;
use Symfony\Component\HttpFoundation\Request;

final class HeadersPropagator implements PropagationGetterInterface
{
    /**
     * @param mixed|Request $carrier
     *
     * @return array<int, string>
     */
    public function keys($carrier): array
    {
        return $carrier->headers->keys();
    }

    /**
     * @param mixed|Request $carrier
     */
    public function get($carrier, string $key): ?string
    {
        return count($carrier->headers->all($key)) > 1
            ? implode(',', $carrier->headers->all($key))
            : $carrier->headers->get($key);
    }
}
