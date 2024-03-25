# Introduction

OpenTelemetry is an observability framework – an API, SDK, and tools that are designed to aid in the generation and collection of application telemetry data such as metrics, logs, and traces.

For documentation of the underlying SDK, visit the [OpenTelemetry PHP SDK documentation](https://opentelemetry.io/docs/languages/php/).

This bundle provides an integration of the OpenTelemetry PHP SDK within your Symfony application.

- Auto registration of OpenTelemetry services (Traces, Meters & Loggers).
- Auto instrumentation of Symfony components.

List of Symfony components that can be instrumented:

- Cache
- Console
- Doctrine
- Http Client
- Http Kernel
- Mailer
- Messenger
- Twig

## Should I use this bundle in production?

No, not yet. This bundle is still in development and should not be used in production or at your own risk.
It misses a few features, and it is not yet tested in a real-world application. It might also not be optimized for performance.
As long as the release goals are not reached and fully defined, no stable version will be released.
But you are welcome to try this bundle and give feedback or contribute to it.

In case you want to try it, a Symfony demo is maintained on this [repository](https://github.com/FriendsOfOpenTelemetry/symfony-demo).

Next: [Getting started](/user-guide/getting-started.md).
