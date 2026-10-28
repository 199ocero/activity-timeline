<?php

use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use JaOcero\ActivityTimeline\Components\ActivityLogSection;
use JaOcero\ActivityTimeline\Support\ActivityLogAdapter;
use JaOcero\ActivityTimeline\Tests\Fixtures\Livewire\TestTimeline;
use JaOcero\ActivityTimeline\Tests\Fixtures\Livewire\TestTimelinePage;
use JaOcero\ActivityTimeline\Tests\Fixtures\Models\Post;
use JaOcero\ActivityTimeline\Tests\Fixtures\Models\User;
use Spatie\Activitylog\Models\Activity;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->user = User::create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);

    actingAs($this->user);
});

describe('adapter', function () {
    it('reads the tracked model changes', function () {
        $post = Post::create(['title' => 'Hello', 'status' => 'draft']);
        $post->update(['status' => 'published']);

        $activity = Activity::query()->where('event', 'updated')->firstOrFail();

        expect(ActivityLogAdapter::getChanges($activity))->toBe([
            'old' => ['status' => 'draft'],
            'attributes' => ['status' => 'published'],
        ]);
    });

    it('reads v4-style changes stored in properties and keeps custom properties apart', function () {
        $activity = new Activity([
            'description' => 'updated',
            'event' => 'updated',
            'properties' => [
                'old' => ['status' => 'draft'],
                'attributes' => ['status' => 'published'],
                'ip' => '127.0.0.1',
            ],
        ]);

        expect(ActivityLogAdapter::getChanges($activity))->toBe([
            'old' => ['status' => 'draft'],
            'attributes' => ['status' => 'published'],
        ])->and(ActivityLogAdapter::getProperties($activity))->toBe(['ip' => '127.0.0.1']);
    });

    it('decodes properties stored as a JSON string', function () {
        $activity = new Activity;
        $activity->setRawAttributes(['properties' => json_encode(['old' => ['a' => 1], 'attributes' => ['a' => 2]])]);

        expect(ActivityLogAdapter::getChanges($activity))->toBe(['old' => ['a' => 1], 'attributes' => ['a' => 2]]);
    });

    it('keeps the 1.x activityData shape for modify_state callbacks', function () {
        $post = Post::create(['title' => 'Hello', 'status' => 'draft']);

        $item = ActivityLogAdapter::toTimelineItem(Activity::query()->firstOrFail());

        expect($item['activityData'])->toHaveKeys(['log_name', 'description', 'subject', 'event', 'causer', 'properties', 'batch_uuid'])
            ->and($item['activityData']['properties']['attributes'])->toBe(['title' => 'Hello', 'status' => 'draft'])
            ->and($item['activityData']['causer']->is($this->user))->toBeTrue()
            ->and($item['event'])->toBe('created');
    });
});

describe('section', function () {
    it('shows the activities of the infolist record, newest first', function () {
        $post = Post::create(['title' => 'Hello', 'status' => 'draft']);
        $this->travel(1)->minutes();
        $post->update(['status' => 'published']);

        Post::create(['title' => 'Another post']);

        TestTimeline::$components = fn (): array => [ActivityLogSection::make()];

        livewire(TestTimeline::class, ['record' => $post])
            ->assertSee('Activities')
            ->assertSeeInOrder(['updated', 'Jane Doe', 'created', 'Jane Doe'])
            ->assertSeeHtml('The <strong>post</strong> was <strong>updated</strong> by <strong>Jane Doe</strong>.')
            ->assertSeeInOrder(['Status', 'draft', 'published'])
            ->assertDontSee('Another post');
    });

    it('logs custom events and escapes their descriptions', function () {
        $post = Post::create(['title' => 'Hello', 'status' => 'draft']);

        activity()->performedOn($post)->causedBy($this->user)->event('published')->log('<b>Shipped</b> it');

        TestTimeline::$components = fn (): array => [ActivityLogSection::make()];

        livewire(TestTimeline::class, ['record' => $post])
            ->assertSeeHtml('&lt;b&gt;Shipped&lt;/b&gt; it')
            ->assertSee('Published');
    });

    it('limits the query and knows when there are more activities', function () {
        $post = Post::create(['title' => 'Hello', 'status' => 'draft']);

        foreach (range(1, 4) as $index) {
            $post->update(['title' => "Hello {$index}"]);
        }

        $section = ActivityLogSection::make()->subject($post)->limit(3);

        expect($section->getActivityItems())->toHaveCount(3)
            ->and($section->hasMoreActivities())->toBeTrue()
            ->and(ActivityLogSection::make()->subject($post)->limit(10)->hasMoreActivities())->toBeFalse();
    });

    it('customizes the query, icons, colors and titles', function () {
        $post = Post::create(['title' => 'Hello', 'status' => 'draft']);
        $post->update(['status' => 'published']);

        TestTimeline::$components = fn (): array => [
            ActivityLogSection::make()
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('event', 'updated'))
                ->eventIcons(['updated' => Heroicon::OutlinedSparkles])
                ->eventColors(['updated' => 'warning'])
                ->getTitleUsing(fn (array $activity): HtmlString => new HtmlString("<em>{$activity['causer']->name} changed it</em>")),
        ];

        livewire(TestTimeline::class, ['record' => $post])
            ->assertSeeHtml('<em>Jane Doe changed it</em>')
            ->assertSeeHtml('--color-500:var(--warning-500)')
            ->assertDontSee('created');
    });
});

describe('page', function () {
    it('renders the timeline and loads more activities on demand', function () {
        $post = Post::create(['title' => 'Hello', 'status' => 'draft']);

        foreach (range(1, 4) as $index) {
            $post->update(['title' => "Hello {$index}"]);
        }

        TestTimelinePage::$configuration = null;
        TestTimelinePage::$perPage = 2;

        livewire(TestTimelinePage::class, ['record' => $post])
            ->assertSee('Hello 4')
            ->assertDontSee('Hello 1')
            ->assertSeeHtml('wire:click="loadMoreActivities"')
            ->call('loadMoreActivities')
            ->assertSee('Hello 1')
            ->assertSeeHtml('wire:click="loadMoreActivities"')
            ->call('loadMoreActivities')
            ->assertDontSeeHtml('wire:click="loadMoreActivities"');
    });

    it('honors the 1.x configuration array', function () {
        $post = Post::create(['title' => 'Hello', 'status' => 'draft']);

        TestTimelinePage::$perPage = 20;
        TestTimelinePage::$configuration = fn (): array => [
            'activity_section' => [
                'label' => 'Legacy activities',
                'aside' => true,
            ],
            'activity_title' => [
                'modify_state' => fn (array $state): HtmlString => new HtmlString("Legacy <b>{$state['event']}</b> by {$state['causer']->name}"),
            ],
            'activity_date' => [
                'date' => 'Y/m/d',
            ],
            'activity_icon' => [
                'icon' => fn (?string $state): ?string => $state === 'created' ? 'heroicon-o-star' : null,
            ],
        ];

        livewire(TestTimelinePage::class, ['record' => $post])
            ->assertSee('Legacy activities')
            ->assertSeeHtml('Legacy <b>created</b> by Jane Doe')
            ->assertSee(now()->format('Y/m/d'))
            ->assertSeeHtml('fi-aside');
    });
});
