<?php

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;
use JaOcero\ActivityTimeline\Components\ActivityBadge;
use JaOcero\ActivityTimeline\Components\ActivityCauser;
use JaOcero\ActivityTimeline\Components\ActivityChanges;
use JaOcero\ActivityTimeline\Components\ActivityDate;
use JaOcero\ActivityTimeline\Components\ActivityDescription;
use JaOcero\ActivityTimeline\Components\ActivityIcon;
use JaOcero\ActivityTimeline\Components\ActivitySection;
use JaOcero\ActivityTimeline\Components\ActivityTitle;
use JaOcero\ActivityTimeline\Enums\IconAnimation;
use JaOcero\ActivityTimeline\Tests\Fixtures\Livewire\TestTimeline;
use Livewire\Features\SupportTesting\Testable;

use function Pest\Livewire\livewire;

/**
 * @param  array<string, mixed>  $state
 * @param  array<Component> | Component  $components
 */
function timeline(array $state, array|Component $components): Testable
{
    TestTimeline::$state = $state;
    TestTimeline::$components = fn (): array => is_array($components) ? $components : [$components];

    return livewire(TestTimeline::class);
}

/**
 * @return array<int, array<string, mixed>>
 */
function articleActivities(): array
{
    return [
        ['title' => 'Published', 'description' => 'Live on the blog.', 'status' => 'published', 'created_at' => now()],
        ['title' => 'Reviewing', 'description' => 'Final touches.', 'status' => 'reviewing', 'created_at' => now()->subDay()],
        ['title' => 'Drafting', 'description' => 'First draft.', 'status' => 'drafting', 'created_at' => now()->subDays(5)],
        ['title' => 'Ideation', 'description' => 'Looking for ideas.', 'status' => 'ideation', 'created_at' => now()->subYears(2)],
    ];
}

/**
 * @return array<Component>
 */
function articleSchema(): array
{
    return [
        ActivityTitle::make('title'),
        ActivityDescription::make('description'),
        ActivityDate::make('created_at')->date('F j, Y'),
        ActivityIcon::make('status')
            ->icon(fn (?string $state): ?Heroicon => match ($state) {
                'published' => Heroicon::RocketLaunch,
                'drafting' => Heroicon::Bolt,
                default => null,
            })
            ->color(fn (?string $state): string => $state === 'published' ? 'success' : 'gray'),
    ];
}

describe('rendering', function () {
    it('renders every item from array state in order', function () {
        timeline(['activities' => articleActivities()], ActivitySection::make('activities')
            ->label('My Activities')
            ->description('These are the recorded activities.')
            ->schema(articleSchema()))
            ->assertSee('My Activities')
            ->assertSee('These are the recorded activities.')
            ->assertSeeInOrder(['Published', 'Reviewing', 'Drafting', 'Ideation'])
            ->assertSee('Live on the blog.')
            ->assertSee(now()->format('F j, Y'))
            ->assertSeeHtml('fi-timeline-item')
            ->assertSeeHtml('datetime="');
    });

    it('renders an icon node with color variables and a dot when there is no icon', function () {
        timeline(['activities' => articleActivities()], ActivitySection::make('activities')->schema(articleSchema()))
            ->assertSeeHtml('fi-timeline-item-node')
            ->assertSeeHtml('--color-500:var(--success-500)')
            ->assertSeeHtml('fi-timeline-item-dot');
    });

    it('animates the icon', function (IconAnimation $animation, string $class) {
        timeline(['activities' => articleActivities()], ActivitySection::make('activities')->schema([
            ActivityTitle::make('title'),
            ActivityIcon::make('status')->icon(Heroicon::Bolt)->animation($animation),
        ]))->assertSeeHtml($class);
    })->with([
        [IconAnimation::Ping, 'animate-ping'],
        [IconAnimation::Spin, 'animate-spin'],
        [IconAnimation::Pulse, 'animate-pulse'],
        [IconAnimation::Bounce, 'animate-bounce'],
    ]);

    it('escapes HTML unless html() is enabled', function () {
        $state = ['activities' => [['title' => '<b>Bold</b>', 'description' => '<script>alert(1)</script><em>Note</em>']]];

        timeline($state, ActivitySection::make('activities')->schema([
            ActivityTitle::make('title'),
            ActivityDescription::make('description'),
        ]))
            ->assertDontSeeHtml('<b>Bold</b>')
            ->assertSeeHtml('&lt;b&gt;Bold&lt;/b&gt;');

        timeline($state, ActivitySection::make('activities')->schema([
            ActivityTitle::make('title')->html(),
            ActivityDescription::make('description')->html(),
        ]))
            ->assertSeeHtml('<b>Bold</b>')
            ->assertSeeHtml('<em>Note</em>')
            ->assertDontSeeHtml('<script>alert(1)</script>');
    });

    it('renders badges, causers and extra components', function () {
        timeline(['activities' => [[
            'title' => 'Updated the plan',
            'event' => 'updated',
            'causer' => ['name' => 'Jane Doe', 'avatar' => null],
            'note' => 'Extra entry content',
        ]]], ActivitySection::make('activities')->schema([
            ActivityCauser::make('causer')->showName(),
            ActivityTitle::make('title'),
            ActivityBadge::make('event'),
            TextEntry::make('note')->hiddenLabel(),
        ]))
            ->assertSeeInOrder(['JD', 'Jane Doe', 'Updated the plan', 'Updated', 'Extra entry content'])
            ->assertSeeHtml('fi-badge')
            ->assertSeeHtml('fi-color-info');
    });

    it('shows placeholders when an entry has no state', function () {
        timeline(['activities' => [['title' => null]]], ActivitySection::make('activities')->schema([
            ActivityTitle::make('title')->placeholder('No title is set'),
        ]))->assertSee('No title is set');
    });
});

describe('layout options', function () {
    it('renders the empty state', function () {
        timeline(['activities' => []], ActivitySection::make('activities')
            ->schema(articleSchema())
            ->emptyStateHeading('Nothing here')
            ->emptyStateDescription('Come back later.'))
            ->assertSee('Nothing here')
            ->assertSee('Come back later.')
            ->assertSeeHtml('fi-timeline-empty-state');
    });

    it('hides items after showItemsCount() until "show more" is clicked', function () {
        $html = timeline(['activities' => articleActivities()], ActivitySection::make('activities')
            ->schema(articleSchema())
            ->showItemsCount(2))
            ->assertSeeHtml('reveal()')
            ->assertSeeHtml('scrollIntoView')
            ->assertSeeHtml('x-ref="lastItem"')
            ->assertSeeHtml('x-collapse')
            ->assertSee('Show more')
            ->html();

        expect(substr_count($html, 'x-show="page > '))->toBe(1);
    });

    it('does not render "show more" when every item fits', function () {
        timeline(['activities' => articleActivities()], ActivitySection::make('activities')
            ->schema(articleSchema())
            ->showItemsCount(10))
            ->assertDontSeeHtml('reveal()');
    });

    it('renders a load more button that calls the Livewire method', function () {
        timeline(['activities' => articleActivities()], ActivitySection::make('activities')
            ->schema(articleSchema())
            ->loadMore('loadMoreActivities', hasMoreItems: true, label: 'Older'))
            ->assertSeeHtml('wire:click="loadMoreActivities"')
            ->assertSee('Older');

        timeline(['activities' => articleActivities()], ActivitySection::make('activities')
            ->schema(articleSchema())
            ->loadMore('loadMoreActivities', hasMoreItems: false))
            ->assertDontSeeHtml('wire:click="loadMoreActivities"');
    });

    it('groups items by date', function () {
        timeline(['activities' => articleActivities()], ActivitySection::make('activities')
            ->schema(articleSchema())
            ->groupByDate())
            ->assertSeeInOrder([
                'Today', 'Published',
                'Yesterday', 'Reviewing',
                now()->subDays(5)->translatedFormat('F j'), 'Drafting',
                now()->subYears(2)->translatedFormat('F j, Y'), 'Ideation',
            ]);
    });

    it('reverses the order', function () {
        timeline(['activities' => articleActivities()], ActivitySection::make('activities')
            ->schema(articleSchema())
            ->reverse())
            ->assertSeeInOrder(['Ideation', 'Drafting', 'Reviewing', 'Published']);
    });

    it('hides the heading and supports compact, flat and aside layouts', function () {
        timeline(['activities' => articleActivities()], ActivitySection::make('activities')
            ->label('Hidden heading')
            ->schema(articleSchema())
            ->headingVisible(false)
            ->compact()
            ->contained(false))
            ->assertDontSee('Hidden heading')
            ->assertSeeHtml('fi-timeline-compact')
            ->assertDontSeeHtml('fi-section fi-contained');

        timeline(['activities' => articleActivities()], ActivitySection::make('activities')
            ->schema(articleSchema())
            ->aside())
            ->assertSeeHtml('fi-aside');
    });
});

describe('dates', function () {
    it('shows relative dates with the full date in a tooltip', function () {
        timeline(['activities' => [['title' => 'Created', 'created_at' => now()->subHours(2)]]], ActivitySection::make('activities')->schema([
            ActivityTitle::make('title'),
            ActivityDate::make('created_at')->relative(),
        ]))
            ->assertSee('2 hours ago')
            ->assertSeeHtml('x-tooltip');
    });

    it('formats dates in the given timezone', function () {
        timeline(['activities' => [['title' => 'Created', 'created_at' => '2026-01-01 23:30:00']]], ActivitySection::make('activities')->schema([
            ActivityTitle::make('title'),
            ActivityDate::make('created_at')->date('Y-m-d H:i', 'Asia/Manila'),
        ]))->assertSee('2026-01-02 07:30');
    });
});

describe('changes', function () {
    it('renders changed attributes from old to new values', function () {
        timeline(['activities' => [[
            'title' => 'Updated',
            'changes' => [
                'old' => ['status' => 'draft', 'title' => 'Same', 'updated_at' => '2026-01-01'],
                'attributes' => ['status' => 'published', 'title' => 'Same', 'updated_at' => '2026-01-02', 'is_public' => true],
            ],
        ]]], ActivitySection::make('activities')->schema([
            ActivityTitle::make('title'),
            ActivityChanges::make('changes')->attributeLabels(['is_public' => 'Visibility']),
        ]))
            ->assertSeeInOrder(['Status', 'draft', 'published'])
            ->assertSeeInOrder(['Visibility', 'true'])
            ->assertDontSee('Updated at')
            ->assertDontSee('Same');
    });
});

describe('1.x compatibility', function () {
    it('keeps modifyState() and allowHtml() working', function () {
        timeline(['activities' => [['data' => ['name' => 'Jane', 'event' => 'created']]]], ActivitySection::make('activities')->schema([
            ActivityTitle::make('data')
                ->allowHtml()
                ->modifyState(fn (array $state): HtmlString => new HtmlString("<strong>{$state['name']}</strong> {$state['event']} it")),
        ]))->assertSeeHtml('<strong>Jane</strong> created it');
    });

    it('keeps the legacy setters', function () {
        timeline(['activities' => articleActivities()], ActivitySection::make('activities')
            ->schema(articleSchema())
            ->showItemsCount(1)
            ->showItemsLabel('View Old')
            ->showItemsIcon('heroicon-m-chevron-down')
            ->showItemsColor('primary')
            ->extraAttributes(['class' => 'my-new-class']))
            ->assertSee('View Old')
            ->assertSeeHtml('my-new-class');
    });
});
