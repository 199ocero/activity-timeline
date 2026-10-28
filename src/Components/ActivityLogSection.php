<?php

namespace JaOcero\ActivityTimeline\Components;

use BackedEnum;
use Closure;
use Filament\Schemas\Components\Component;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use JaOcero\ActivityTimeline\Support\ActivityLogAdapter;

/**
 * An `ActivitySection` that loads Spatie Activitylog records for a subject.
 *
 * Drop it into any infolist (e.g. a resource's view page) and it shows the
 * activities of the current record:
 *
 * ```php
 * ActivityLogSection::make()
 * ```
 */
class ActivityLogSection extends ActivitySection
{
    protected Model|Closure|null $subject = null;

    protected ?Closure $modifyQueryUsing = null;

    protected int|Closure|null $limit = null;

    /**
     * @var array<string, string|BackedEnum> | Closure
     */
    protected array|Closure $eventIcons = [];

    /**
     * @var array<string, string|array<int|string, string>> | Closure
     */
    protected array|Closure $eventColors = [];

    /**
     * @var array<string, string> | Closure
     */
    protected array|Closure $attributeLabels = [];

    protected ?Closure $formatChangeValueUsing = null;

    protected ?Closure $getTitleUsing = null;

    protected ?Closure $getDescriptionUsing = null;

    /**
     * @var array<int, array<string, mixed>> | null
     */
    protected ?array $cachedActivityItems = null;

    protected ?string $cachedActivityItemsKey = null;

    protected bool $hasMoreActivities = false;

    public static function getDefaultName(): ?string
    {
        return 'activities';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('activity-timeline::timeline.section.label'));
        $this->description(__('activity-timeline::timeline.section.description'));

        $this->state(static fn (ActivityLogSection $component): array => $component->getActivityItems());

        $this->schema(static fn (ActivityLogSection $component): array => $component->getDefaultTimelineComponents());
    }

    /**
     * The model whose activities are shown. Defaults to the record of the infolist.
     */
    public function subject(Model|Closure|null $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function modifyQueryUsing(?Closure $callback): static
    {
        $this->modifyQueryUsing = $callback;

        return $this;
    }

    /**
     * Only query the latest `$limit` activities.
     */
    public function limit(int|Closure|null $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @param  array<string, string|BackedEnum> | Closure  $icons  Icons keyed by event name.
     */
    public function eventIcons(array|Closure $icons): static
    {
        $this->eventIcons = $icons;

        return $this;
    }

    /**
     * @param  array<string, string|array<int|string, string>> | Closure  $colors  Colors keyed by event name.
     */
    public function eventColors(array|Closure $colors): static
    {
        $this->eventColors = $colors;

        return $this;
    }

    /**
     * @param  array<string, string> | Closure  $labels  Labels for the changed attributes, keyed by attribute name.
     */
    public function attributeLabels(array|Closure $labels): static
    {
        $this->attributeLabels = $labels;

        return $this;
    }

    /**
     * Format a changed value for display. The callback receives `$value` and `$attribute`.
     */
    public function formatChangeValueUsing(?Closure $callback): static
    {
        $this->formatChangeValueUsing = $callback;

        return $this;
    }

    /**
     * The callback receives the normalized `$activity` array and the `Activity` `$record`.
     */
    public function getTitleUsing(?Closure $callback): static
    {
        $this->getTitleUsing = $callback;

        return $this;
    }

    /**
     * The callback receives the normalized `$activity` array and the `Activity` `$record`.
     */
    public function getDescriptionUsing(?Closure $callback): static
    {
        $this->getDescriptionUsing = $callback;

        return $this;
    }

    public function getSubject(): ?Model
    {
        return $this->evaluate($this->subject) ?? $this->getRecord();
    }

    public function getLimit(): ?int
    {
        $limit = $this->evaluate($this->limit);

        return filled($limit) ? max((int) $limit, 1) : null;
    }

    public function hasMoreActivities(): bool
    {
        $this->getActivityItems();

        return $this->hasMoreActivities;
    }

    /**
     * @return Builder<Model>
     */
    public function getActivitiesQuery(Model $subject): Builder
    {
        $query = ActivityLogAdapter::query($subject);

        if ($this->modifyQueryUsing) {
            $query = $this->evaluate($this->modifyQueryUsing, [
                'query' => $query,
                'subject' => $subject,
            ]) ?? $query;
        }

        return $query;
    }

    /**
     * Load and normalize the activities once per subject and limit.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getActivityItems(): array
    {
        $subject = $this->getSubject();

        if (! $subject?->exists) {
            return [];
        }

        $limit = $this->getLimit();
        $cacheKey = $subject::class.':'.$subject->getKey().':'.($limit ?? 'all');

        if (($this->cachedActivityItems !== null) && ($this->cachedActivityItemsKey === $cacheKey)) {
            return $this->cachedActivityItems;
        }

        $query = $this->getActivitiesQuery($subject);

        if ($limit !== null) {
            // Query one extra row to know whether a "Load more" button is needed.
            $query->limit($limit + 1);
        }

        $activities = $query->get();

        $this->hasMoreActivities = ($limit !== null) && ($activities->count() > $limit);

        if ($this->hasMoreActivities) {
            $activities = $activities->take($limit);
        }

        $items = [];

        foreach ($activities as $activity) {
            // The subject is the record we queried for, so skip the extra query.
            $activity->setRelation('subject', $subject);

            $item = ActivityLogAdapter::toTimelineItem($activity);
            $item['title'] = $this->getActivityTitle($item, $activity);
            $item['description_html'] = $this->getActivityDescription($item, $activity);

            $items[] = $item;
        }

        $this->cachedActivityItemsKey = $cacheKey;

        return $this->cachedActivityItems = $items;
    }

    /**
     * @return array<Component>
     */
    public function getDefaultTimelineComponents(): array
    {
        return [
            ActivityIcon::make('event')
                ->icon(fn (?string $state): string|BackedEnum|null => $this->getEventIcon($state))
                ->color(fn (?string $state): string|array => $this->getEventColor($state)),
            ActivityCauser::make('causer'),
            ActivityTitle::make('title')
                ->html(),
            ActivityBadge::make('event'),
            ActivityDate::make('created_at')
                ->relative(),
            ActivityDescription::make('description_html')
                ->html(),
            $this->makeChangesEntry(),
        ];
    }

    public function makeChangesEntry(): ActivityChanges
    {
        return ActivityChanges::make('changes')
            ->attributeLabels(fn (): array => $this->evaluate($this->attributeLabels) ?? [])
            ->formatValueUsing($this->formatChangeValueUsing);
    }

    public function getEventIcon(?string $event): string|BackedEnum|null
    {
        $icons = $this->evaluate($this->eventIcons) ?? [];

        if (filled($event) && array_key_exists($event, $icons)) {
            return $icons[$event];
        }

        return match ($event) {
            'created' => Heroicon::OutlinedPlus,
            'updated' => Heroicon::OutlinedPencilSquare,
            'deleted' => Heroicon::OutlinedTrash,
            'restored' => Heroicon::OutlinedArrowUturnLeft,
            default => Heroicon::OutlinedBolt,
        };
    }

    /**
     * @return string | array<int|string, string>
     */
    public function getEventColor(?string $event): string|array
    {
        $colors = $this->evaluate($this->eventColors) ?? [];

        if (filled($event) && array_key_exists($event, $colors)) {
            return $colors[$event];
        }

        return match ($event) {
            'created' => 'success',
            'updated' => 'info',
            'deleted' => 'danger',
            'restored' => 'warning',
            default => 'primary',
        };
    }

    /**
     * @param  array<string, mixed>  $activity
     */
    protected function getActivityTitle(array $activity, Model $record): string|Htmlable|null
    {
        if ($this->getTitleUsing) {
            return $this->evaluate($this->getTitleUsing, [
                'activity' => $activity,
                'record' => $record,
            ]);
        }

        return static::getDefaultActivityTitle($activity);
    }

    /**
     * @param  array<string, mixed>  $activity
     */
    protected function getActivityDescription(array $activity, Model $record): string|Htmlable|null
    {
        if (! $this->getDescriptionUsing) {
            return null;
        }

        return $this->evaluate($this->getDescriptionUsing, [
            'activity' => $activity,
            'record' => $record,
        ]);
    }

    /**
     * "The project was updated by Jane Doe." for model events, or the logged description otherwise.
     *
     * @param  array<string, mixed>  $activity
     */
    public static function getDefaultActivityTitle(array $activity): Htmlable
    {
        $description = (string) ($activity['description'] ?? '');
        $event = $activity['event'] ?? null;

        if (filled($event) && ($description === $event)) {
            $subject = $activity['subject'] ?? null;
            $causer = $activity['causer'] ?? null;

            $subjectLabel = $subject instanceof Model
                ? Str::lower(Str::snake(class_basename($subject), ' '))
                : __('activity-timeline::timeline.activity_log.unknown_subject');

            $causerName = $causer instanceof Model ? ActivityCauser::guessModelName($causer) : null;

            $eventTranslationKey = "activity-timeline::timeline.events.{$event}";
            $eventLabel = Lang::has($eventTranslationKey) ? __($eventTranslationKey) : $event;

            return new HtmlString(__(
                filled($causerName)
                    ? 'activity-timeline::timeline.activity_log.title'
                    : 'activity-timeline::timeline.activity_log.title_without_causer',
                [
                    'subject' => e($subjectLabel),
                    'event' => e(Str::lower($eventLabel)),
                    'causer' => e((string) $causerName),
                ],
            ));
        }

        return new HtmlString(e($description));
    }
}
