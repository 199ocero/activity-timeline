<?php

namespace JaOcero\ActivityTimeline\Concerns;

use BackedEnum;
use Closure;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use JaOcero\ActivityTimeline\Components\ActivityBadge;
use JaOcero\ActivityTimeline\Components\ActivityCauser;
use JaOcero\ActivityTimeline\Components\ActivityDate;
use JaOcero\ActivityTimeline\Components\ActivityDescription;
use JaOcero\ActivityTimeline\Components\ActivityIcon;
use JaOcero\ActivityTimeline\Components\ActivityLogSection;
use JaOcero\ActivityTimeline\Components\ActivityTitle;

/**
 * Builds a Spatie Activitylog timeline for the page's record.
 *
 * Customize it by overriding `configureActivitySection()` and `modifyActivitiesQuery()`.
 * The 1.x `configuration()` array is still honored.
 */
trait HasSetting
{
    public int $activitiesPage = 1;

    /**
     * @deprecated Override `configureActivitySection()` instead. Any keys returned here are merged over the defaults.
     *
     * @return array<string, array<string, mixed>>
     */
    protected function configuration(): array
    {
        return [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function getDefaultActivityTimelineConfiguration(): array
    {
        return [
            'activity_section' => [
                'label' => __('activity-timeline::timeline.section.label'),
                'description' => __('activity-timeline::timeline.section.description'),
                'show_items_count' => 0,
                'show_items_label' => null,
                'show_items_icon' => null,
                'show_items_color' => 'gray',
                'aside' => false,
                'empty_state_heading' => null,
                'empty_state_description' => null,
                'empty_state_icon' => null,
                'heading_visible' => true,
                'extra_attributes' => [],
            ],
            'activity_title' => [
                'placeholder' => null,
                'allow_html' => true,
            ],
            'activity_description' => [
                'placeholder' => null,
                'allow_html' => true,
            ],
            'activity_date' => [
                'name' => 'created_at',
                'date' => null,
                'placeholder' => null,
            ],
            'activity_icon' => [
                'icon' => null,
                'color' => null,
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function getActivityTimelineConfiguration(): array
    {
        return array_replace_recursive($this->getDefaultActivityTimelineConfiguration(), $this->configuration());
    }

    public function activityInfolist(Schema $schema): Schema
    {
        return $schema
            ->record($this->getRecord())
            ->components([
                $this->configureActivitySection($this->makeActivitySection()),
            ]);
    }

    /**
     * Override to customize the timeline, e.g. `$section->eventIcons([...])->compact()`.
     */
    protected function configureActivitySection(ActivityLogSection $section): ActivityLogSection
    {
        return $section;
    }

    /**
     * Override to scope the activities, e.g. `$query->inLog('orders')`.
     *
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    protected function modifyActivitiesQuery(Builder $query): Builder
    {
        return $query;
    }

    protected function getActivitiesPerPage(): int
    {
        return 20;
    }

    public function loadMoreActivities(): void
    {
        $this->activitiesPage++;
    }

    protected function makeActivitySection(): ActivityLogSection
    {
        $configuration = $this->getActivityTimelineConfiguration();
        $section = $configuration['activity_section'];

        $timeline = ActivityLogSection::make('activities')
            ->subject(fn (): Model => $this->getRecord())
            ->modifyQueryUsing(fn (Builder $query): Builder => $this->modifyActivitiesQuery($query))
            ->limit(fn (): int => $this->getActivitiesPerPage() * $this->activitiesPage)
            ->loadMore('loadMoreActivities', fn (ActivityLogSection $component): bool => $component->hasMoreActivities())
            ->groupByDate()
            ->label($section['label'])
            ->description($section['description'])
            ->showItemsCount($section['show_items_count'])
            ->showItemsLabel($section['show_items_label'])
            ->showItemsIcon($section['show_items_icon'])
            ->showItemsColor($section['show_items_color'])
            ->aside($section['aside'])
            ->emptyStateHeading($section['empty_state_heading'])
            ->emptyStateDescription($section['empty_state_description'])
            ->emptyStateIcon($section['empty_state_icon'])
            ->headingVisible($section['heading_visible'])
            ->extraAttributes($section['extra_attributes']);

        return $timeline->schema(fn (ActivityLogSection $component): array => $this->getLegacyTimelineComponents($component, $configuration));
    }

    /**
     * The default components, with the 1.x configuration applied on top.
     *
     * @param  array<string, array<string, mixed>>  $configuration
     * @return array<Component>
     */
    protected function getLegacyTimelineComponents(ActivityLogSection $timeline, array $configuration): array
    {
        $userConfiguration = $this->configuration();

        $title = $configuration['activity_title'];
        $description = $configuration['activity_description'];
        $date = $configuration['activity_date'];
        $icon = $configuration['activity_icon'];

        $titleEntry = isset($userConfiguration['activity_title']['modify_state'])
            ? ActivityTitle::make('activityData')->modifyState($userConfiguration['activity_title']['modify_state'])
            : ActivityTitle::make('title');

        $descriptionEntry = isset($userConfiguration['activity_description']['modify_state'])
            ? ActivityDescription::make('activityData')->modifyState($userConfiguration['activity_description']['modify_state'])
            : ActivityDescription::make('description_html');

        $dateEntry = ActivityDate::make($date['name'])
            ->placeholder($date['placeholder']);

        if (filled($date['date'])) {
            $dateEntry->dateTime($date['date'])->sinceTooltip();
        } else {
            $dateEntry->relative();
        }

        if (isset($userConfiguration['activity_date']['modify_state'])) {
            $dateEntry->modifyState($userConfiguration['activity_date']['modify_state']);
        }

        return [
            ActivityIcon::make('event')
                ->icon(fn (?string $state): string|BackedEnum|null => static::evaluateLegacyCallback($icon['icon'], $state) ?? $timeline->getEventIcon($state))
                ->color(fn (?string $state): string|array => static::evaluateLegacyCallback($icon['color'], $state) ?? $timeline->getEventColor($state)),
            ActivityCauser::make('causer'),
            $titleEntry
                ->placeholder($title['placeholder'])
                ->allowHtml($title['allow_html']),
            ActivityBadge::make('event'),
            $dateEntry,
            $descriptionEntry
                ->placeholder($description['placeholder'])
                ->allowHtml($description['allow_html']),
            $timeline->makeChangesEntry(),
        ];
    }

    protected static function evaluateLegacyCallback(mixed $callback, ?string $state): mixed
    {
        return $callback instanceof Closure ? $callback($state) : $callback;
    }
}
