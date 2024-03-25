# Getting Started

## Installation

Run the following command to install it in your application:

```bash
composer require friendsofopentelemetry/opentelemetry-bundle
```

### Supported Versions

There is no stable version yet, so you can use the `dev` version to install the bundle.

| Version | Branch | PHP    | OpenTelemetry | Symfony |
|---------|--------|--------|---------------|---------|
| dev     | `main` | `^8.2` | `^1.0`        | `^7.0`  |

## Usage

This bundle is not yet available using Symfony Flex, so you have to manually configure it.

In your `config/bundles.php` file, add the following line at the end of the array:

```php
return [
    // ...
    FriendsOfOpenTelemetry\OpenTelemetryBundle::class => ['all' => true],
];
```

Then, create a new file `config/packages/open_telemetry.yaml` and add the following minimal configuration:

```yaml
open_telemetry:
  service:
    namespace: 'MyCompany'
    name: 'MyApp'
    version: '1.0.0'
    environment: '%kernel.environment%'
```

For further details on the configuration, please refer to the [Configuration page](/user-guide/configuration.md).

Next: [Instrumentation - Introduction](/instrumentation/introduction.md).
