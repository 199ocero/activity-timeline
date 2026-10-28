<?php

namespace JaOcero\ActivityTimeline\Actions;

use Closure;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use JaOcero\ActivityTimeline\Components\ActivityLogSection;

/**
 * Opens a slide-over with the Spatie Activitylog timeline of the action's record.
 *
 * ```php
 * ->recordActions([
 *     ActivityTimelineAction::make(),
 * ])
 * ```
 */
class ActivityTimelineAction extends Action
{
    protected ?Closure $modifyTimelineUsing = null;

    public static function getDefaultName(): ?string
    {
        return 'activityTimeline';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('activity-timeline::timeline.action.label'));

        $this->icon(Heroicon::OutlinedClock);

        $this->color('gray');

        $this->slideOver();

        $this->modalWidth(Width::Large);

        $this->modalHeading(fn (?Model $record): string => __('activity-timeline::timeline.action.modal_heading', [
            'record' => $this->getRecordTitle($record),
        ]));

        $this->modalSubmitAction(false);

        $this->modalCancelActionLabel(__('activity-timeline::timeline.action.close'));

        $this->schema(fn (?Model $record): array => [
            $this->makeTimeline($record),
        ]);
    }

    /**
     * Customize the timeline. The callback receives the `$timeline` (an `ActivityLogSection`).
     */
    public function timeline(?Closure $callback): static
    {
        $this->modifyTimelineUsing = $callback;

        return $this;
    }

    protected function makeTimeline(?Model $record): ActivityLogSection
    {
        $timeline = ActivityLogSection::make('activities')
            ->subject($record)
            ->hiddenLabel()
            ->headingVisible(false)
            ->contained(false)
            ->compact()
            ->groupByDate()
            ->limit(50)
            ->showItemsCount(15);

        if (! $this->modifyTimelineUsing) {
            return $timeline;
        }

        return $this->evaluate($this->modifyTimelineUsing, [
            'timeline' => $timeline,
        ]) ?? $timeline;
    }
}
