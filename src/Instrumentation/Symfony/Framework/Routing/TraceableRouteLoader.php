<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Framework\Routing;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Attribute\Traceable;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class TraceableRouteLoader implements LoaderInterface
{
    public const DEFAULT_KEY = '_traceable';
    public const TRACER_KEY = '_tracer';
    public const OPTION_KEY = 'traceable';

    public function __construct(private LoaderInterface $loader)
    {
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        $routes = $this->loader->load($resource, $type);

        /** @var Route $route */
        foreach ($routes as $route) {
            self::parseAttribute($route);

            $traceable = $route->getOption(self::OPTION_KEY);
            if (null !== $traceable) {
                $route->addDefaults([
                    self::DEFAULT_KEY => $traceable,
                    self::TRACER_KEY => $route->getOption(self::TRACER_KEY),
                ]);
            }
        }

        return $routes;
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return $this->loader->supports($resource, $type);
    }

    public function getResolver(): LoaderResolverInterface
    {
        return $this->loader->getResolver();
    }

    public function setResolver(LoaderResolverInterface $resolver): void
    {
        $this->loader->setResolver($resolver);
    }

    private static function parseAttribute(Route $route): void
    {
        try {
            $controller = $route->getDefault('_controller');
            if (true === str_contains($controller, '::')) {
                if (PHP_VERSION_ID < 80300) {
                    $reflection = new \ReflectionMethod($controller);
                } else {
                    $reflection = \ReflectionMethod::createFromMethodName($controller);
                }
            } else {
                $reflection = new \ReflectionClass($controller);
            }
        } catch (\ReflectionException) {
            return;
        }

        if ($reflection instanceof \ReflectionMethod) {
            $attribute = $reflection->getAttributes(Traceable::class)[0] ?? $reflection->getDeclaringClass()->getAttributes(Traceable::class)[0] ?? null;
        } else {
            $attribute = $reflection->getAttributes(Traceable::class)[0] ?? null;
        }

        if (null !== $attribute) {
            $traceable = $attribute->newInstance();
            $route->addOptions([
                self::OPTION_KEY => true,
                self::TRACER_KEY => $traceable->tracer ?? null,
            ]);
        }
    }
}
