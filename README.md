# A filament plugin to display a chronological sequence of events or activities.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jaocero/activity-timeline.svg?style=flat-square)](https://packagist.org/packages/jaocero/activity-timeline)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/jaocero/activity-timeline/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/jaocero/activity-timeline/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jaocero/activity-timeline/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/jaocero/activity-timeline/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/jaocero/activity-timeline.svg?style=flat-square)](https://packagist.org/packages/jaocero/activity-timeline)



This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require jaocero/activity-timeline
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="activity-timeline-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="activity-timeline-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="activity-timeline-views"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$activityTimeline = new JaOcero\ActivityTimeline();
echo $activityTimeline->echoPhrase('Hello, JaOcero!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Jay-Are Ocero](https://github.com/199ocero)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
