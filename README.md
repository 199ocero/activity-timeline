# Activity Timeline

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jaocero/activity-timeline.svg?style=flat-square)](https://packagist.org/packages/jaocero/activity-timeline)
[![Total Downloads](https://img.shields.io/packagist/dt/jaocero/activity-timeline.svg?style=flat-square)](https://packagist.org/packages/jaocero/activity-timeline)

Activity Timeline plugin conveniently presents upcoming, ongoing, and past activities, offering a comprehensive view of events.

## Installation

You can install the package via composer:

```bash
composer require jaocero/activity-timeline
```

To adhere to Filament's theming approach, you'll be required to employ a personalized theme in order to utilize this plugin.

> **Custom Theme Installation**
> [Filament Docs](https://filamentphp.com/docs/3.x/panels/themes#creating-a-custom-theme)

Add the plugin's views to your `tailwind.config.js` file.

```js
content: [
    ...
    './vendor/jaocero/activity-timeline/resources/views/**/*.blade.php',
]
```

## Usage
Currently, this plugin is exclusively accessible within the Infolists builder. Below is the code demonstrating its usage. Also, it solely functions with `->state([])` and doesn't yet support the use of `->record()`.

```php
public function activityTimelineInfolist(Infolist $infolist): Infolist
{
    return $infolist
        ->state([
            'activities' => [
                [
                    'title' => 'Published Article - Published with Laravel Filament and Tailwind CSS',
                    'description' => 'Approved and published.',
                    'status' => 'published',
                    'created_at' => now()->addDays(8),
                ],
                [
                    'title' => 'Reviewing Article - Final Touches',
                    'description' => 'Reviewing the article and making it ready for publication.',
                    'status' => 'reviewing',
                    'created_at' => now()->addDays(5),
                ],
                [
                    'title' => 'Drafting Article - Make it ready for review',
                    'description' => 'Drafting the article and making it ready for review.',
                    'status' => 'drafting',
                    'created_at' => now()->addDays(2),
                ],
                [
                    'title' => 'Ideation - Looking for Ideas',
                    'description' => 'Idea for my article.',
                    'status' => 'ideation',
                    'created_at' => now()->subDays(7),
                ]
            ]
        ])
        ->schema([
            ActivitySection::make('activities')
                ->label('My Activities')
                ->description('These are the activities that have been recorded.')
                ->schema([
                    ActivityDate::make('created_at')
                        ->date('F j, Y g:i A', 'Asia/Manila')
                        ->placeholder('No date is set.'),
                    ActivityIcon::make('status')
                        ->icon(fn (string | null $state): string | null => match ($state) {
                            'ideation' => 'heroicon-m-light-bulb',
                            'drafting' => 'heroicon-m-bolt',
                            'reviewing' => 'heroicon-m-document-magnifying-glass',
                            'published' => 'heroicon-m-rocket-launch',
                            default => null,
                        })
                        ->color(fn (string | null $state): string | null => match ($state) {
                            'ideation' => 'purple',
                            'drafting' => 'info',
                            'reviewing' => 'warning',
                            'published' => 'success',
                            default => 'gray',
                        })
                        ->size(IconEntrySize::Medium),
                    ActivityBadge::make('status')
                        ->color(fn (string | null $state): string | null => match ($state) {
                            'ideation' => 'purple',
                            'drafting' => 'info',
                            'reviewing' => 'warning',
                            'published' => 'success',
                            default => 'gray',
                        })
                        ->size(BadgeSize::Medium)
                        ->placeholder('No status'),
                    ActivityTitle::make('title')
                        ->placeholder('No title is set'),
                    ActivityDescription::make('description')
                        ->placeholder('No description is set'),
                ])
                ->showItemsCount(2) // Show up to 2 items
                ->showItemsLabel('View Old') // Show "View Old" as link label
                ->showItemsIcon('heroicon-m-chevron-down') // Show button icon
                ->showItemsColor('gray') // Show button color and it supports all colors
                ->aside(true)
        ]);
}
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
