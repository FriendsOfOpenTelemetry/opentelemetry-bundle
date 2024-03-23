# Getting Started

## Installation

The bundle requires PHP 8.2 or higher, Symfony 7 or higher. Run the following command to install it in your application:

```bash
composer require friendsofopentelemetry/opentelemetry-bundle
```

### Versions

| Version | Branch | PHP     | Symfony |
|---------|--------|---------|---------|
| dev     | `main` | `>=8.2` | `^7.0`  |

## Usage

This bundle is not yet available using Symfony Flex, so you have to manually configure it.

In your `config/bundles.php` file, add the following line at the end of the array:

```php
return [
    // ...
    FriendsOfOpenTelemetry\OpenTelemetryBundle::class => ['all' => true],
];
```

Then, create a new file `config/packages/open_telemetry.yaml` and add the following configuration:

```yaml
open_telemetry:
  service:
    namespace: 'MyCompany'
    name: 'MyApp'
    version: '1.0.0'
    environment: '%kernel.environment%'
```
