<?php

namespace JaOcero\ActivityTimeline\Concerns;

use Filament\Infolists\Infolist;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use JaOcero\ActivityTimeline\Components\ActivityDate;
use JaOcero\ActivityTimeline\Components\ActivityDescription;
use JaOcero\ActivityTimeline\Components\ActivityIcon;
use JaOcero\ActivityTimeline\Components\ActivitySection;
use JaOcero\ActivityTimeline\Components\ActivityTitle;

trait HasSetting
{
    protected function configuration(): array
    {
        return [
            'activity_section' => [
                'label' => 'Activities',
                'description' => 'These are the activities that have been recorded.',
                'show_items_count' => 0,
                'show_items_label' => 'Show more',
                'show_items_icon' => 'heroicon-o-chevron-right',
                'show_items_color' => 'gray',
                'aside' => true,
                'empty_state_heading' => 'No activities yet',
                'empty_state_description' => 'Check back later for activities that have been recorded.',
                'empty_state_icon' => 'heroicon-o-bolt-slash',
                'heading_visible' => true,
                'extra_attributes' => [],
            ],
            'activity_title' => [
                'placeholder' => 'No title is set',
                'allow_html' => true,
            ],
            'activity_description' => [
                'placeholder' => 'No description is set',
                'allow_html' => true,
            ],
            'activity_date' => [
                'name' => 'created_at',
                'date' => 'F j, Y g:i A',
                'placeholder' => 'No date is set',
            ],
            'activity_icon' => [
                'icon' => fn (?string $state): ?string => match ($state) {
                    default => null
                },
                'color' => fn (?string $state): ?string => match ($state) {
                    default => null
                },
            ],
        ];
    }

    public function activityInfolist(Infolist $infolist): Infolist
    {
        $activityTitle = $this->modifiedState()['activity_title']['modify_state'];
        $activityDescription = $this->modifiedState()['activity_description']['modify_state'];
        $activityDate = $this->configuration()['activity_date']['modify_state'];

        if (isset($this->configuration()['activity_title']['modify_state'])) {
            $activityTitle = $this->configuration()['activity_title']['modify_state'];
        }

        if (isset($this->configuration()['activity_description']['modify_state'])) {
            $activityDescription = $this->configuration()['activity_description']['modify_state'];
        }
        if (isset($this->configuration()['activity_date']['modify_state'])) {
            $activityDate = $this->configuration()['activity_date']['modify_state'];
        }

        return $infolist
            ->state([
                'activities' => $this->getActivityLogRecord(),
            ])
            ->schema([
                ActivitySection::make('activities')
                    ->label($this->configuration()['activity_section']['label'])
                    ->description($this->configuration()['activity_section']['description'])
                    ->schema([
                        ActivityTitle::make('activityData')
                            ->placeholder($this->configuration()['activity_title']['placeholder'])
                            ->allowHtml($this->configuration()['activity_title']['allow_html'])
                            ->modifyState($activityTitle),
                        ActivityDescription::make('activityData')
                            ->placeholder($this->configuration()['activity_description']['placeholder'])
                            ->allowHtml($this->configuration()['activity_description']['allow_html'])
                            ->modifyState($activityDescription),
                        ActivityDate::make($this->configuration()['activity_date']['name'])
                            ->date($this->configuration()['activity_date']['date'])
                            ->placeholder($this->configuration()['activity_date']['placeholder'])
                            ->allowHtml($this->configuration()['activity_description']['allow_html'])
                            ->modifyState($activityDate),
                        ActivityIcon::make('event')
                            ->icon($this->configuration()['activity_icon']['icon'])
                            ->color($this->configuration()['activity_icon']['color']),
                    ])
                    ->showItemsCount($this->configuration()['activity_section']['show_items_count'])
                    ->showItemsLabel($this->configuration()['activity_section']['show_items_label'])
                    ->showItemsIcon($this->configuration()['activity_section']['show_items_icon'])
                    ->showItemsColor($this->configuration()['activity_section']['show_items_color'])
                    ->aside($this->configuration()['activity_section']['aside'])
                    ->emptyStateHeading($this->configuration()['activity_section']['empty_state_heading'])
                    ->emptyStateDescription($this->configuration()['activity_section']['empty_state_description'])
                    ->emptyStateIcon($this->configuration()['activity_section']['empty_state_icon'])
                    ->headingVisible($this->configuration()['activity_section']['heading_visible'])
                    ->extraAttributes($this->configuration()['activity_section']['extra_attributes']),
            ])
            ->columns(1);
    }

    protected function getActivites(): \Illuminate\Database\Eloquent\Collection
    {
        $activityModelClass = config('activitylog.activity_model');
        $activityModel = new $activityModelClass;

        return $activityModel::query()
            ->with(['causer', 'subject'])
            ->where('subject_id', $this->record->id)
            ->where('subject_type', get_class($this->record))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    private function getActivityLogRecord(): Collection
    {
        $activities = $this->getActivites();

        $activities->transform(function ($activity) {

            $activity->activityData = [
                'log_name' => $activity->log_name,
                'description' => $activity->description,
                'subject' => $activity->subject,
                'event' => $activity->event,
                'causer' => $activity->causer,
                'properties' => json_decode($activity->properties, true),
                'batch_uuid' => $activity->batch_uuid,
            ];

            return $activity;
        });

        return $activities;
    }

    private static function formatValue($value)
    {
        if ($value === null) {
            return '—';
        }
        if (is_array($value)) {
            return json_encode($value);
        }

        return $value;
    }

    private function modifiedState(): array
    {
        return [
            'activity_title' => [
                'modify_state' => function (array $state) {
                    if ($state['description'] == $state['event']) {
                        $className = Str::lower(Str::snake(class_basename($state['subject']), ' '));
                        $causerName = $state['causer']->name ?? $state['causer']->first_name ?? $state['causer']->last_name ?? $state['causer']->username ?? 'Unknown';

                        return new HtmlString(sprintf('The <strong>%s</strong> was <strong>%s</strong> by <strong>%s</strong>.', $className, $state['event'], $causerName));
                    }

                    return new HtmlString($state['description']);
                },
            ],
            'activity_description' => [
                'modify_state' => function (array $state) {

                    $properties = $state['properties'];

                    if (! empty($properties) && isset($properties['old']) && isset($properties['attributes'])) {

                        $oldValues = $properties['old'];
                        $newValues = $properties['attributes'];

                        $changes = [];

                        foreach ($newValues as $key => $newValue) {
                            $oldValue = self::formatValue($oldValues[$key] ?? null);
                            $newValue = self::formatValue($newValue);

                            if ($oldValue != $newValue) {
                                $changes[] = "- {$key} from <strong>".htmlspecialchars($oldValue).'</strong> to <strong>'.htmlspecialchars($newValue).'</strong>';
                            }
                        }

                        $causerName = $state['causer']->name ?? $state['causer']->first_name ?? $state['causer']->last_name ?? $state['causer']->username ?? 'Unknown';

                        return new HtmlString(sprintf('%s %s the following: <br>%s', $causerName, $state['event'], implode('<br>', $changes)));
                    }

                    return null;
                },
            ],
        ];
    }
}
