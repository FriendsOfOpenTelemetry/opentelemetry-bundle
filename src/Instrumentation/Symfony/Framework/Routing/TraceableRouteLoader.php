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
            $attribute = $this->parseAttribute($route);

            if (null !== $attribute) {
                $traceable = $attribute->newInstance();
                $route->addOptions([
                    self::OPTION_KEY => true,
                    self::TRACER_KEY => $traceable->tracer ?? null,
                ]);

                $route->addDefaults([
                    self::DEFAULT_KEY => true,
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

    /**
     * @return \ReflectionAttribute<Traceable>|null
     */
    private function parseAttribute(Route $route): ?\ReflectionAttribute
    {
        try {
            $controller = $route->getDefault('_controller');
            if (true === str_contains($controller, '::')) {
                $reflection = new \ReflectionMethod($controller);

                $attribute = $reflection->getAttributes(Traceable::class)[0] ?? null;

                if (null !== $attribute) {
                    return $attribute;
                }

                $reflection = $reflection->getDeclaringClass();
            } else {
                $reflection = new \ReflectionClass($controller);
            }
        } catch (\ReflectionException) {
            return null;
        }

        return $reflection->getAttributes(Traceable::class)[0] ?? null;
    }
}
