# friendsofopentelemetry/opentelemetry-bundle

[![GitHub Actions: CI][github-actions-ci-badge]][github-actions-ci-page]
[![GitHub Actions: Docs][github-actions-docs-badge]][github-actions-docs-page]
[![Codecov: Coverage][codecov-badge]][codecov-page]
[![Coveralls: Coverage][coveralls-badge]][coveralls-page]
[![Project stage: Development][project-stage-badge]][project-stage-page]
[![Built with Nix][build-with-nix-badge]][build-with-nix-page]
[![FOSSA Status][fossa-status-badge]][fossa-status-page]
[![Packagist Version][packagist-version-badge]][packagist-page]
[![Packagist Downloads][packagist-downloads-badge]][packagist-page]

> Traces, metrics, and logs instrumentation within your Symfony application.

## Overview

OpenTelemetry is an observability framework – an API, SDK, and tools that are designed to aid in the generation and collection of application telemetry data such as metrics, logs, and traces.

For more information, visit the [OpenTelemetry PHP SDK documentation](https://opentelemetry.io/docs/languages/php/).

This bundle provides a seamless integration of the OpenTelemetry PHP SDK within your Symfony application.

## Documentation

Please read the documentation: <https://friendsofopentelemetry.github.io/opentelemetry-bundle/>

## Contributing

Found a bug, have a suggestion for a new feature? Please read the [contribution guide](CONTRIBUTING.md) and submit an issue.

## Versioning

This section outlines how pre-release versions will be published until the first stable release is achieved and the goals for each stage.

Backward compatibility will not be provided during pre-release stages, as our focus is on progressing toward the stable release. Consequently, breaking changes, such as package upgrades and configuration modifications, may occur without notice.

### Alpha

The goal of the Alpha release is to achieve full integration of the tracing, metering, and logging services provided by the OpenTelemetry SDK.

To accomplish this:

- All services and their dependencies must be fully declared in the bundle configuration.
- Services should be easily overridden by user-defined service definitions following Symfony's dependency injection principles.

Once the services are functional according to OpenTelemetry SDK capabilities and Symfony's service definitions, this phase will be complete.

### Beta

The goal of the Beta release is to enable tracing instrumentation for a Symfony application, either automatically or selectively.

Instrumentation goals include:

- Allowing instrumentation of Symfony components through defined entry points.
- Providing automatic (opt-out) instrumentation, where components are instrumented by default, with the option to exclude specific components.
- Enabling selective instrumentation, allowing users to explicitly choose which components to instrument through provided APIs.

For more details on instrumentation requirements, refer to the [Traces Instrumentation Documentation](https://friendsofopentelemetry.github.io/opentelemetry-bundle/instrumentation/traces.html#components).

This phase will conclude once:

- All components are fully covered by instrumentation.
- Both automatic and selective configuration methods are implemented.

### Stable

To complete the Stable release, the following goals must be met:

- Full compliance with [OpenTelemetry Trace Semantic Conventions](https://opentelemetry.io/docs/specs/semconv/general/trace/).
- Stabilization of tracing instrumentation, incorporating feedback and addressing reported issues.
- Comprehensive test coverage for each component.
- A complete documentation set, covering tracing services, configurations, and bundle capabilities.

## Credits

- [OpenTelemetry PHP](https://opentelemetry.io/docs/languages/php/)
- [Symfony SDK for Sentry](https://github.com/getsentry/sentry-symfony/)

## License

All the code in this repository is released under the MIT License, for more information take a look at the [LICENSE](LICENSE) file.

[![FOSSA Status][fossa-status-badge-large]][fossa-status-page]

## Repo Activity

![Repo Activity][repobeats-image]

[github-actions-ci-badge]: https://github.com/FriendsOfOpenTelemetry/opentelemetry-bundle/actions/workflows/ci.yml/badge.svg
[github-actions-ci-page]: https://github.com/FriendsOfOpenTelemetry/opentelemetry-bundle/actions/workflows/ci.yml
[github-actions-docs-badge]: https://github.com/FriendsOfOpenTelemetry/opentelemetry-bundle/actions/workflows/docs.yml/badge.svg
[github-actions-docs-page]: https://github.com/FriendsOfOpenTelemetry/opentelemetry-bundle/actions/workflows/docs.yml
[codecov-badge]: https://codecov.io/gh/FriendsOfOpenTelemetry/opentelemetry-bundle/graph/badge.svg?token=XkThYaxqli
[codecov-page]: https://codecov.io/gh/FriendsOfOpenTelemetry/opentelemetry-bundle
[coveralls-badge]: https://img.shields.io/coverallsCoverage/github/FriendsOfOpenTelemetry/opentelemetry-bundle?logo=coveralls&label=coveralls
[coveralls-page]: https://coveralls.io/github/FriendsOfOpenTelemetry/opentelemetry-bundle
[build-with-nix-badge]: https://img.shields.io/badge/Built_With-Nix-5277C3.svg?logo=nixos
[build-with-nix-page]: https://builtwithnix.org/
[project-stage-badge]: https://img.shields.io/badge/Project_Stage-Development-yellowgreen.svg
[project-stage-page]: https://blog.pother.ca/project-stages/
[fossa-status-badge]: https://app.fossa.com/api/projects/custom%2B42279%2Fgithub.com%2FFriendsOfOpenTelemetry%2Fopentelemetry-bundle.svg?type=shield
[fossa-status-badge-large]: https://app.fossa.com/api/projects/custom%2B42279%2Fgithub.com%2FFriendsOfOpenTelemetry%2Fopentelemetry-bundle.svg?type=large
[fossa-status-page]: https://app.fossa.com/projects/custom%252B42279%252Fgithub.com%252FFriendsOfOpenTelemetry%252Fopentelemetry-bundle
[packagist-version-badge]: https://img.shields.io/packagist/v/friendsofopentelemetry/opentelemetry-bundle
[packagist-downloads-badge]: https://img.shields.io/packagist/dt/friendsofopentelemetry/opentelemetry-bundle
[packagist-page]: https://packagist.org/packages/friendsofopentelemetry/opentelemetry-bundle
[repobeats-image]: https://repobeats.axiom.co/api/embed/27664db040411ce770316b3bf7577564ded32e04.svg
